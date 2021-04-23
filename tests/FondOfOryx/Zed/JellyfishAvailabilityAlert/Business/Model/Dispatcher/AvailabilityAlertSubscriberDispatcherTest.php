<?php

namespace FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Model\Dispatcher;

use Codeception\Test\Unit;
use Exception;
use FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapter;
use FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapterInterface;
use Generated\Shared\Transfer\AvailabilityAlertSubscriberTransfer;
use Monolog\Logger;
use Spryker\Shared\Log\Config\LoggerConfigInterface;

class AvailabilityAlertSubscriberDispatcherTest extends Unit
{
    /**
     * @var \Monolog\Logger|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $loggerMock;

    /**
     * @var \Generated\Shared\Transfer\AvailabilityAlertSubscriberTransfer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $subscriberTransferMock;

    /**
     * @var \FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $adapterMock;

    /**
     * @var \FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Model\Dispatcher\AvailabilityAlertSubscriberDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @return void
     */
    public function _before()
    {
        parent::_before();

        $this->adapterMock = static::getMockBuilder(AvailabilityAlertAdapter::class)->disableOriginalConstructor()->getMock();
        $this->loggerMock = static::getMockBuilder(Logger::class)->disableOriginalConstructor()->getMock();
        $this->subscriberTransferMock = static::getMockBuilder(AvailabilityAlertSubscriberTransfer::class)->disableOriginalConstructor()->getMock();

        $this->dispatcher = new class ($this->adapterMock, $this->loggerMock) extends AvailabilityAlertSubscriberDispatcher
        {
            /**
             * @var \Monolog\Logger
             */
            protected $logger;

            /**
             *  constructor.
             *
             * @param \FondOfOryx\Zed\JellyfishAvailabilityAlert\Business\Api\Adapter\AvailabilityAlertAdapterInterface $adapter
             * @param \Monolog\Logger $logger
             */
            public function __construct(AvailabilityAlertAdapterInterface $adapter, Logger $logger)
            {
                parent::__construct($adapter);
                $this->logger = $logger;
            }

            /**
             * @param \Spryker\Shared\Log\Config\LoggerConfigInterface|null $loggerConfig
             *
             * @return \Monolog\Logger
             */
            public function getLogger(?LoggerConfigInterface $loggerConfig = null)
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
        $this->dispatcher->dispatch($this->subscriberTransferMock);
    }

    /**
     * @return void
     */
    public function testDispatchThrowsException(): void
    {
        $this->adapterMock->expects(static::once())->method('sendRequest')->willThrowException(new Exception('test'));
        $this->subscriberTransferMock->expects(static::once())->method('getIdAvailabilityAlertSubscriber')->willReturn(1);
        $this->loggerMock->expects(static::once())->method('error');

        $exception = null;
        try {
            $this->dispatcher->dispatch($this->subscriberTransferMock);
        } catch (Exception $e) {
            $exception = $e;
        }

        static::assertNotNull($exception);
    }
}