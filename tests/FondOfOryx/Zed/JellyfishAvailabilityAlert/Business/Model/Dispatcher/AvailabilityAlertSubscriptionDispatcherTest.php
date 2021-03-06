<?php

namespace FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Model\Dispatcher;

use Codeception\Test\Unit;
use Exception;
use FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapter;
use FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapterInterface;
use Generated\Shared\Transfer\AvailabilityAlertSubscriptionTransfer;
use Psr\Log\LoggerInterface;
use Spryker\Shared\Log\Config\LoggerConfigInterface;

class AvailabilityAlertSubscriptionDispatcherTest extends Unit
{
    /**
     * @var \Monolog\Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Generated\Shared\Transfer\AvailabilityAlertSubscriptionTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscriptionTransferMock;

    /**
     * @var \FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $adapterMock;

    /**
     * @var \FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Model\Dispatcher\AvailabilityAlertSubscriptionDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @return void
     */
    public function _before()
    {
        parent::_before();

        $this->adapterMock = $this->getMockBuilder(AvailabilityAlertAdapter::class)->disableOriginalConstructor()->getMock();
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->disableOriginalConstructor()->getMock();
        $this->subscriptionTransferMock = $this->getMockBuilder(AvailabilityAlertSubscriptionTransfer::class)->disableOriginalConstructor()->getMock();

        $this->dispatcher = new class ($this->adapterMock, $this->loggerMock) extends AvailabilityAlertSubscriptionDispatcher
        {
            /**
             * @var \Psr\Log\LoggerInterface
             */
            protected $logger;

            /**
             *  constructor.
             *
             * @param \FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapterInterface $adapter
             * @param \Psr\Log\LoggerInterface $logger
             */
            public function __construct(AvailabilityAlertAdapterInterface $adapter, LoggerInterface $logger)
            {
                parent::__construct($adapter);
                $this->logger = $logger;
            }

            /**
             * @param \Spryker\Shared\Log\Config\LoggerConfigInterface|null $loggerConfig
             *
             * @return \Psr\Log\LoggerInterface
             */
            public function getLogger(?LoggerConfigInterface $loggerConfig = null): LoggerInterface
            {
                return $this->logger;
            }
        };
    }

    /**
     * @return void
     */
    public function testDispatch(): void
    {
        $this->adapterMock->expects(static::once())->method('sendRequest');
        $this->dispatcher->dispatch($this->subscriptionTransferMock);
    }

    /**
     * @return void
     */
    public function testDispatchThrowsException(): void
    {
        $this->adapterMock->expects(static::once())->method('sendRequest')->willThrowException(new Exception('test'));
        $this->subscriptionTransferMock->expects(static::once())->method('getIdAvailabilityAlertSubscription')->willReturn(1);
        $this->loggerMock->expects(static::once())->method('error');

        $exception = null;
        try {
            $this->dispatcher->dispatch($this->subscriptionTransferMock);
        } catch (Exception $e) {
            $exception = $e;
        }

        static::assertNotNull($exception);
    }
}
