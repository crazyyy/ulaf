/**
 * @class Provider
 * @classdesc Represents an email provider configuration.
 */
class Provider {
	static allowedNames = [
		'other',
		'sendgrid',
		'mailgun',
		'brevo',
		'amazon_ses',
		'postmark',
	];

	/**
	 * @class
	 * @param {string}       [id='']           - The provider ID.
	 * @param {string}       [name='']         - The provider name.
	 * @param {string}       [description='']  - The provider description.
	 * @param {string}       [fromEmail='']    - The email address used as the sender.
	 * @param {string}       [fromName='']     - The name used as the sender.
	 * @param {string}       [smtpHost='']     - The SMTP host.
	 * @param {number}       [smtpPort=0]      - The SMTP port.
	 * @param {string}       [smtpSecure='']   - The SMTP secure connection type.
	 * @param {'yes' | 'no'} [smtpAuth='no']   - Indicates if SMTP authentication is required.
	 * @param {string}       [smtpUsername=''] - The SMTP username.
	 * @param {string}       [smtpPassword=''] - The SMTP password.
	 */
	constructor(
		id = '',
		name = '',
		description = '',
		fromEmail = '',
		fromName = '',
		smtpHost = '',
		smtpPort = 0,
		smtpSecure = '',
		smtpAuth = 'no',
		smtpUsername = '',
		smtpPassword = ''
	) {
		if (!Provider.allowedNames.includes(name)) {
			throw new Error(
				`Invalid provider name: ${name}. Allowed names are: ${Provider.allowedNames.join(
					', '
				)}`
			);
		}

		this.id = id;
		this.from_email = fromEmail;
		this.from_name = fromName;
		this.smtp_host = smtpHost;
		this.smtp_port = smtpPort;
		this.smtp_secure = smtpSecure;
		this.smtp_auth = smtpAuth;
		this.smtp_username = smtpUsername;
		this.smtp_password = smtpPassword;
		this.name = name;
		this.description = description;
	}
}

export default Provider;
