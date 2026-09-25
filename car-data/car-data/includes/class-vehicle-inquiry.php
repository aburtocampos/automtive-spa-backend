<?php

namespace AutomotiveInventory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Vehicle_Inquiry {

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	public function register_routes(): void {
		register_rest_route(
			'automotive/v1',
			'/inquiries',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'handle_inquiry' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	public function handle_inquiry( \WP_REST_Request $request ): \WP_REST_Response {

		$name         = sanitize_text_field( $request->get_param( 'name' ) );
		$email        = sanitize_email( $request->get_param( 'email' ) );
		$phone        = sanitize_text_field( $request->get_param( 'phone' ) );
		$request_type = sanitize_text_field( $request->get_param( 'requestType' ) );
		$vehicle_name = sanitize_text_field( $request->get_param( 'vehicleName' ) );
		$vehicle_year = absint( $request->get_param( 'vehicleYear' ) );
		$message      = sanitize_textarea_field( $request->get_param( 'message' ) );

		if ( empty( $name ) ) {
			return $this->error_response(
				'Name is required.',
				400
			);
		}

		if ( empty( $email ) || ! is_email( $email ) ) {
			return $this->error_response(
				'A valid email address is required.',
				400
			);
		}

		if ( empty( $vehicle_name ) ) {
			return $this->error_response(
				'Vehicle is required.',
				400
			);
		}

		$allowed_request_types = [
			'quote',
			'test-drive',
		];

		if ( ! in_array( $request_type, $allowed_request_types, true ) ) {
			return $this->error_response(
				'Invalid request type.',
				400
			);
		}

		$request_label = 'quote' === $request_type
			? 'Solicitud de cotización'
			: 'Prueba de manejo';

		$recipient = get_option( 'admin_email' );

		$subject = sprintf(
			'%s - %s',
			$request_label,
			$vehicle_name
		);

		$email_body = $this->build_email_template(
			[
				'name'          => $name,
				'email'         => $email,
				'phone'         => $phone,
				'request_label' => $request_label,
				'vehicle_name'  => $vehicle_name,
				'vehicle_year'  => $vehicle_year,
				'message'       => $message,
			]
		);

		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'Reply-To: ' . $name . ' <' . $email . '>',
		];

		$sent = wp_mail(
			$recipient,
			$subject,
			$email_body,
			$headers
		);

		if ( ! $sent ) {
			return $this->error_response(
				'The email could not be sent.',
				500
			);
		}

		return new \WP_REST_Response(
			[
				'success' => true,
				'message' => 'Inquiry sent successfully.',
			],
			200
		);
	}

	private function build_email_template( array $data ): string {

		$name          = esc_html( $data['name'] );
		$email         = esc_html( $data['email'] );
		$phone         = esc_html( $data['phone'] );
		$request_label = esc_html( $data['request_label'] );
		$vehicle_name  = esc_html( $data['vehicle_name'] );
		$vehicle_year  = absint( $data['vehicle_year'] );
		$message       = nl2br( esc_html( $data['message'] ) );

		$vehicle_title = $vehicle_name;

		if ( $vehicle_year ) {
			$vehicle_title .= ' ' . $vehicle_year;
		}

		$phone_row = '';

		if ( ! empty( $phone ) ) {
			$phone_row = '
				<tr>
					<td style="
						padding: 16px 0;
						border-bottom: 1px solid #e5e7eb;
						font-size: 14px;
						color: #6b7280;
						width: 140px;
						vertical-align: top;
					">
						Teléfono
					</td>

					<td style="
						padding: 16px 0;
						border-bottom: 1px solid #e5e7eb;
						font-size: 15px;
						font-weight: 600;
						color: #111827;
						vertical-align: top;
					">
						' . $phone . '
					</td>
				</tr>
			';
		}

		$message_section = '';

		if ( ! empty( $message ) ) {
			$message_section = '
				<div style="
					margin-top: 32px;
					padding: 24px;
					background: #f9fafb;
					border: 1px solid #e5e7eb;
					border-radius: 12px;
				">
					<p style="
						margin: 0 0 10px;
						font-size: 12px;
						font-weight: 700;
						letter-spacing: 0.08em;
						text-transform: uppercase;
						color: #6b7280;
					">
						Mensaje
					</p>

					<p style="
						margin: 0;
						font-size: 15px;
						line-height: 1.7;
						color: #374151;
					">
						' . $message . '
					</p>
				</div>
			';
		}

		return '
			<!doctype html>
			<html lang="es">
			<head>
				<meta charset="UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1.0">
				<title>' . $request_label . '</title>
			</head>

			<body style="
				margin: 0;
				padding: 0;
				background-color: #f3f4f6;
				font-family: Arial, Helvetica, sans-serif;
				color: #111827;
			">

				<table
					role="presentation"
					width="100%"
					cellspacing="0"
					cellpadding="0"
					border="0"
					style="
						width: 100%;
						background-color: #f3f4f6;
						padding: 40px 16px;
					"
				>
					<tr>
						<td align="center">

							<table
								role="presentation"
								width="100%"
								cellspacing="0"
								cellpadding="0"
								border="0"
								style="
									width: 100%;
									max-width: 640px;
									background-color: #ffffff;
									border-radius: 16px;
									overflow: hidden;
									border: 1px solid #e5e7eb;
								"
							>

								<tr>
									<td style="
										background-color: #111315;
										padding: 34px 36px;
									">

										<p style="
											margin: 0 0 10px;
											font-size: 12px;
											font-weight: 700;
											letter-spacing: 0.12em;
											text-transform: uppercase;
											color: #aeb2b7;
										">
											Catalogo Pellas Prueba Técnica
										</p>

										<h1 style="
											margin: 0;
											font-size: 27px;
											line-height: 1.25;
											font-weight: 700;
											color: #ffffff;
										">
											' . $request_label . '
										</h1>

										<p style="
											margin: 12px 0 0;
											font-size: 16px;
											line-height: 1.5;
											color: #d1d5db;
										">
											' . $vehicle_title . '
										</p>

									</td>
								</tr>

								<tr>
									<td style="padding: 36px;">

										<p style="
											margin: 0 0 8px;
											font-size: 13px;
											font-weight: 700;
											letter-spacing: 0.08em;
											text-transform: uppercase;
											color: #6b7280;
										">
											Nueva solicitud
										</p>

										<h2 style="
											margin: 0 0 12px;
											font-size: 22px;
											line-height: 1.35;
											color: #111827;
										">
											Información del cliente
										</h2>

										<p style="
											margin: 0 0 26px;
											font-size: 15px;
											line-height: 1.6;
											color: #6b7280;
										">
											Se recibió una nueva solicitud desde el catálogo de vehículos.
										</p>

										<table
											role="presentation"
											width="100%"
											cellspacing="0"
											cellpadding="0"
											border="0"
											style="width: 100%;"
										>

											<tr>
												<td style="
													padding: 16px 0;
													border-bottom: 1px solid #e5e7eb;
													font-size: 14px;
													color: #6b7280;
													width: 140px;
													vertical-align: top;
												">
													Nombre
												</td>

												<td style="
													padding: 16px 0;
													border-bottom: 1px solid #e5e7eb;
													font-size: 15px;
													font-weight: 600;
													color: #111827;
													vertical-align: top;
												">
													' . $name . '
												</td>
											</tr>

											<tr>
												<td style="
													padding: 16px 0;
													border-bottom: 1px solid #e5e7eb;
													font-size: 14px;
													color: #6b7280;
													width: 140px;
													vertical-align: top;
												">
													Email
												</td>

												<td style="
													padding: 16px 0;
													border-bottom: 1px solid #e5e7eb;
													font-size: 15px;
													font-weight: 600;
													color: #111827;
													vertical-align: top;
												">
													<a
														href="mailto:' . $email . '"
														style="
															color: #111827;
															text-decoration: none;
														"
													>
														' . $email . '
													</a>
												</td>
											</tr>

											' . $phone_row . '

											<tr>
												<td style="
													padding: 16px 0;
													border-bottom: 1px solid #e5e7eb;
													font-size: 14px;
													color: #6b7280;
													width: 140px;
													vertical-align: top;
												">
													Tipo
												</td>

												<td style="
													padding: 16px 0;
													border-bottom: 1px solid #e5e7eb;
													font-size: 15px;
													font-weight: 600;
													color: #111827;
													vertical-align: top;
												">
													' . $request_label . '
												</td>
											</tr>

											<tr>
												<td style="
													padding: 16px 0;
													font-size: 14px;
													color: #6b7280;
													width: 140px;
													vertical-align: top;
												">
													Vehículo
												</td>

												<td style="
													padding: 16px 0;
													font-size: 15px;
													font-weight: 600;
													color: #111827;
													vertical-align: top;
												">
													' . $vehicle_title . '
												</td>
											</tr>

										</table>

										' . $message_section . '

										<div style="
											margin-top: 32px;
											text-align: center;
										">
											<a
												href="mailto:' . $email . '"
												style="
													display: inline-block;
													padding: 14px 24px;
													background-color: #111315;
													color: #ffffff;
													font-size: 14px;
													font-weight: 700;
													text-decoration: none;
													border-radius: 8px;
												"
											>
												Responder al cliente
											</a>
										</div>

									</td>
								</tr>

								<tr>
									<td style="
										padding: 22px 36px;
										background-color: #f9fafb;
										border-top: 1px solid #e5e7eb;
										text-align: center;
									">

										<p style="
											margin: 0;
											font-size: 12px;
											line-height: 1.6;
											color: #9ca3af;
										">
											Este correo fue generado automáticamente desde Catalogo Pellas Prueba Técnica.
										</p>

									</td>
								</tr>

							</table>

						</td>
					</tr>
				</table>

			</body>
			</html>
		';
	}

	private function error_response(
		string $message,
		int $status
	): \WP_REST_Response {

		return new \WP_REST_Response(
			[
				'success' => false,
				'message' => $message,
			],
			$status
		);
	}
}