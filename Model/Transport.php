<?php
namespace Hostinghelden\Smtp\Model;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\EmailMessageInterface;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Mail\TransportInterface;
use Magento\Framework\Phrase;
use Magento\Store\Model\ScopeInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Sendmail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Magento\Framework\Encryption\EncryptorInterface;

				/* $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log'); */
				/* $logger = new \Zend\Log\Logger(); */
				/* $logger->addWriter($writer); */
				/* $logger->info($options); */


class Transport extends SmtpTransport implements TransportInterface {

    const XML_SET_RETURN_PATH = 'system/smtp/set_return_path';
    const XML_RETURN_EMAIL = 'system/smtp/return_path_email';
    const XML_SMTP_HOST = 'system/smtp/host';
    const XML_SMTP_USER = 'system/smtp/username';
    const XML_SMTP_PASSWORD = 'system/smtp/password';
    const XML_SMTP_PORT = 'system/smtp/port';
    const XML_SMTP_METHOD = 'system/smtp/authentication';
    const XML_SMTP_SSL = 'system/smtp/protocol';

    /**
     * @var int
     */
    private $isSetReturnPath;

    /**
     * @var string|null
     */
    private $returnPathValue;
    /**
     * @var MessageInterface
     */
    protected $_message;

    /**
     * @var Sendmail
     */
    private $zendTransport;

    /**
     * @param EncryptorInterface $enc
     * @param EmailMessageInterface $message Email message object
     * @param ScopeConfigInterface $scopeConfig Core store config
     * @param null|string|array|\Traversable $parameters Config options for sendmail parameters
     */
    public function __construct(
        EncryptorInterface $enc,
        EmailMessageInterface $message,
        ScopeConfigInterface $scopeConfig,
        $parameters = null
    ) {

        $this->isSetReturnPath = (int) $scopeConfig->getValue( self::XML_SET_RETURN_PATH, ScopeInterface::SCOPE_STORE);
        $this->returnPathValue = $scopeConfig->getValue( self::XML_RETURN_EMAIL, ScopeInterface::SCOPE_STORE);
        $host = $scopeConfig->getValue( self::XML_SMTP_HOST, ScopeInterface::SCOPE_STORE);
        $port = $scopeConfig->getValue( self::XML_SMTP_PORT, ScopeInterface::SCOPE_STORE);
        $user = $scopeConfig->getValue( self::XML_SMTP_USER, ScopeInterface::SCOPE_STORE);
        $ssl = $scopeConfig->getValue( self::XML_SMTP_SSL, ScopeInterface::SCOPE_STORE);
        $method = $scopeConfig->getValue( self::XML_SMTP_METHOD, ScopeInterface::SCOPE_STORE);

        $password = $scopeConfig->getValue( self::XML_SMTP_PASSWORD, ScopeInterface::SCOPE_STORE);
				$pw = $enc->decrypt($password);

				$options = [
					'name' => 'smtp',
					'host' => $host,
					'port' => $port,
					'connection_class'  => $method,
					'connection_config' => [
						'username' => $user,
						'password' => $pw,
						'ssl' => $ssl
					],
				];

				$this->zendTransport = new SmtpTransport();
				$smtpConf = new SmtpOptions($options);
				$this->zendTransport->setOptions($smtpConf);
				$this->_message = $message;
				parent::__construct($smtpConf);
		}

		/**
		 * @inheritdoc
		 */
		public function getMessage() {
			return $this->_message;
		}

		/**
		 * @inheritdoc
		 */
		public function sendMessage() {
			try {
				$zendMessage = Message::fromString($this->_message->getRawMessage())->setEncoding('utf-8');

				// workaround for magento 2.3.3
				$zendMessage->getHeaders()->removeHeader("Content-Disposition");

				if (2 === $this->isSetReturnPath && $this->returnPathValue) {
					$zendMessage->setSender($this->returnPathValue);
				} elseif (1 === $this->isSetReturnPath && $zendMessage->getFrom()->count()) {
					$fromAddressList = $zendMessage->getFrom();
					$fromAddressList->rewind();
					$zendMessage->setSender($fromAddressList->current()->getEmail());
				}

				$this->zendTransport->send($zendMessage);
			} catch (\Exception $e) {
				throw new MailException(new Phrase($e->getMessage()), $e);
			}
		}
}

?>
