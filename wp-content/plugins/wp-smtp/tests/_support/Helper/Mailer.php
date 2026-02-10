<?php

namespace Helper;

use Codeception\Module;
use Codeception\TestInterface;
use SolidWP\Mail\Connectors\ConnectorPostmark;
use SolidWP\Mail\Connectors\ConnectorSMTP;
use SolidWP\Mail\Repository\ProvidersRepository;
use SolidWP\Mail\SolidMailer;
use WP_Error;

class Mailer extends Module {

	private ProvidersRepository $providers_repository;

	// phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
	public function _before( TestInterface $test ) {
		$this->providers_repository = new ProvidersRepository();
	}

	public function haveSuccessfulConnection( array $data ): void {
		$this->createConnection( $data, true );
	}

	public function haveFailedConnection( array $data ): void {
		$this->createConnection( $data, false );
	}

	public function haveInvalidConnection( array $data ): void {
		unset( $data['smtp_port'] );
		$this->providers_repository->save( new ConnectorSMTP( $data ) );
	}

	private function createConnection( array $data, bool $is_success ): void {
		$apiKey                = uniqid();
		$data['smtp_username'] = $apiKey;
		$this->providers_repository->save( new ConnectorPostmark( $data ) );
		add_filter(
			'pre_http_request',
			static function ( $result, $args, $url ) use ( $apiKey, $is_success ) {
				if ( $url !== ConnectorPostmark::API_ENDPOINT ) {
					return $result;
				}

				if ( $args['headers']['X-Postmark-Server-Token'] !== $apiKey ) {
					return $result;
				}

				return $is_success ? [
					'response' => [
						'code' => 200,
					],
					'body'     => wp_json_encode( [ 'MessageID' => '1' ] ),
				] : new WP_Error( 'error', 'Fake Error' );
			},
			10,
			3
		);
	}

	public function seeSolidMailer(): bool {
		return $GLOBALS['phpmailer'] instanceof SolidMailer;
	}

	public function dontSeeSolidMailer(): bool {
		return ! $GLOBALS['phpmailer'] instanceof SolidMailer;
	}
}
