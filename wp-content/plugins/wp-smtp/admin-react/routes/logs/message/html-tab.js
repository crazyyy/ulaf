/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { StyledMessageTab } from './styles';

class HTMLTab extends Component {
	constructor(props) {
		super(props);

		this.state = {
			height: 0,
		};

		this.onLoad = this.onLoad.bind(this);
		this.onRef = this.onRef.bind(this);
	}

	onLoad() {
		if (this.ref) {
			this.setState({
				height: this.ref.contentWindow.document.body.scrollHeight,
			});

			const links = this.ref.contentWindow.document.querySelectorAll('a');
			for (const link of links) {
				link.setAttribute('target', '_blank');
			}
		}
	}

	onRef(ref) {
		this.ref = ref;

		if (this.state.height === 0) {
			const height = ref.contentWindow.document.body.scrollHeight;

			if (height) {
				this.setState({ height });
			}
		}
	}

	render() {
		return (
			<StyledMessageTab variant="info">
				<iframe
					srcDoc={this.props.email.message}
					width="100%"
					height={this.state.height}
					onLoad={this.onLoad}
					ref={this.onRef}
					title={__('Message Preview', 'LION')}
					sandbox="allow-same-origin allow-top-navigation-by-user-activation allow-popups"
				/>
			</StyledMessageTab>
		);
	}
}

export default HTMLTab;
