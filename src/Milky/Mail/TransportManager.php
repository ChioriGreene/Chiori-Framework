<?php namespace Milky\Mail;

use Aws\Ses\SesClient;
use GuzzleHttp\Client as HttpClient;
use Milky\Binding\UniversalBuilder;
use Milky\Facades\Config;
use Milky\Helpers\Arr;
use Milky\Impl\Manager;
use Milky\Mail\Transport\LogTransport;
use Milky\Mail\Transport\MailgunTransport;
use Milky\Mail\Transport\MandrillTransport;
use Milky\Mail\Transport\SesTransport;
use Milky\Mail\Transport\SparkPostTransport;
use Swift_MailTransport as MailTransport;
use Swift_SendmailTransport as SendmailTransport;
use Swift_SmtpTransport as SmtpTransport;

/**
 * The MIT License (MIT)
 * Copyright 2017 Penoaks Publishing Ltd. <development@penoaks.org>
 *
 * This Source Code is subject to the terms of the MIT License.
 * If a copy of the license was not distributed with this file,
 * You can obtain one at https://opensource.org/licenses/MIT.
 */
class TransportManager extends Manager
{
	/**
	 * Create an instance of the SMTP Swift Transport driver.
	 *
	 * @return \Swift_SmtpTransport
	 */
	protected function createSmtpDriver()
	{
		$config = Config::get( 'mail' );

		// The Swift SMTP transport instance will allow us to use any SMTP backend
		// for delivering mail such as Sendgrid, Amazon SES, or a custom server
		// a developer has available. We will just pass this configured host.
		$transport = SmtpTransport::newInstance( $config['host'], $config['port'] );

		if ( isset( $config['encryption'] ) )
			$transport->setEncryption( $config['encryption'] );

		// Once we have the transport we will check for the presence of a username
		// and password. If we have it we will set the credentials on the Swift
		// transporter instance so that we'll properly authenticate delivery.
		if ( isset( $config['username'] ) )
		{
			$transport->setUsername( $config['username'] );
			$transport->setPassword( $config['password'] );
		}

		if ( isset( $config['stream'] ) )
			$transport->setStreamOptions( $config['stream'] );

		return $transport;
	}

	/**
	 * Create an instance of the Sendmail Swift Transport driver.
	 *
	 * @return \Swift_SendmailTransport
	 */
	protected function createSendmailDriver()
	{
		$command = Config::get( 'mail.sendmail' );

		return SendmailTransport::newInstance( $command );
	}

	/**
	 * Create an instance of the Amazon SES Swift Transport driver.
	 *
	 * @return SesTransport
	 */
	protected function createSesDriver()
	{
		$config = Config::get( 'services.ses' );

		$config += [
			'version' => 'latest',
			'service' => 'email',
		];

		if ( $config['key'] && $config['secret'] )
		{
			$config['credentials'] = Arr::only( $config, ['key', 'secret'] );
		}

		return new SesTransport( new SesClient( $config ) );
	}

	/**
	 * Create an instance of the Mail Swift Transport driver.
	 *
	 * @return \Swift_MailTransport
	 */
	protected function createMailDriver()
	{
		return MailTransport::newInstance();
	}

	/**
	 * Create an instance of the Mailgun Swift Transport driver.
	 *
	 * @return MailgunTransport
	 */
	protected function createMailgunDriver()
	{
		$config = Config::get( 'services.mailgun', [] );

		return new MailgunTransport( $this->getHttpClient( $config ), $config['secret'], $config['domain'] );
	}

	/**
	 * Create an instance of the Mandrill Swift Transport driver.
	 *
	 * @return MandrillTransport
	 */
	protected function createMandrillDriver()
	{
		$config = Config::get( 'services.mandrill', [] );

		return new MandrillTransport( $this->getHttpClient( $config ), $config['secret'] );
	}

	/**
	 * Create an instance of the SparkPost Swift Transport driver.
	 *
	 * @return SparkPostTransport
	 */
	protected function createSparkPostDriver()
	{
		$config = Config::get( 'services.sparkpost', [] );

		return new SparkPostTransport( $this->getHttpClient( $config ), $config['secret'], Arr::get( $config, 'options', [] ) );
	}

	/**
	 * Create an instance of the Log Swift Transport driver.
	 *
	 * @return LogTransport
	 */
	protected function createLogDriver()
	{
		return new LogTransport( UniversalBuilder::resolveClass( 'Psr\Log\LoggerInterface' ) );
	}

	/**
	 * Get a fresh Guzzle HTTP client instance.
	 *
	 * @param  array $config
	 * @return HttpClient
	 */
	protected function getHttpClient( $config )
	{
		$guzzleConfig = Arr::get( $config, 'guzzle', [] );

		return new HttpClient( Arr::add( $guzzleConfig, 'connect_timeout', 60 ) );
	}

	/**
	 * Get the default mail driver name.
	 *
	 * @return string
	 */
	public function getDefaultDriver()
	{
		return Config::get( 'mail.driver' );
	}

	/**
	 * Set the default mail driver name.
	 *
	 * @param  string $name
	 * @return void
	 */
	public function setDefaultDriver( $name )
	{
		Config::set( 'mail.driver', $name );
	}
}
