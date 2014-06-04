/** @jsx React.DOM */
var Link = React.createClass ({
	render: function () {
		var link = this.props.link;
		link.type = link.service.toLowerCase ().replace ('+', '-plus');
		link.class_name = 'fa fa-' + link.type;
		if (link.type === 'website') {
			link.class_name = 'fa fa-external-link';
		}
		return (
			<div className="link">
				<span className={link.class_name}></span>
				<a href={link.link}>{link.link}</a>
			</div>
		);
	}
});

var LinkList = React.createClass ({
	render: function () {
		var links = [];
		//console.log (this.props.links);
		this.props.links.forEach (function (link) {
			links.push (<Link link={link} key={link.id} />);
		});
		return (<div id="link-list">{links}</div>);
	}
});

var LinkForm = React.createClass ({
	render: function () {
		return (
			<form onSubmit={this.handleSubmit}>
				<p>
					<input type="text" ref="handle" placeholder={this.props.i18n.link_placeholder} size="35" />
					<select ref="service">
						<option value="">{this.props.i18n.service}</option>
						<option value="Facebook">Facebook</option>
						<option value="Google+">Google+</option>
						<option value="Instagram">Instagram</option>
						<option value="Twitter">Twitter</option>
						<option value="Tumblr">Tumblr</option>
						<option value="Website">Website</option>
						<option value="YouTube">YouTube</option>
					</select>
					<input type="submit" value={this.props.i18n.add_link} />
				</p>
			</form>
		);
	},
	
	handleSubmit: function (event) {
		event.preventDefault ();

		var handle = this.refs.handle.getDOMNode ().value,
			service_node = this.refs.service.getDOMNode (),
			service = service_node.options[service_node.selectedIndex].value;

		this.props.onLinkSubmit ({handle: handle, service: service});
		this.refs.handle.getDOMNode ().value = '';
		service_node.selectedIndex = 0;
	}
});

var LinkBox = React.createClass ({
	getInitialState: function () {
		return {links: []};
	},
	
	loadLinksFromServer: function () {
		$.ajax ({
			url: this.props.init_url,
			dataType: 'json',
			success: function (res) {
				if (! res.success) {
					//console.log (res.error);
				} else {
					this.setState ({links: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				//console.log (err.toString ());
			}.bind (this)
		});
	},
	
	handleLinkSubmit: function (data) {
		data.user = this.props.user_id;
		//console.log (data);
		$.ajax ({
			url: this.props.add_url,
			dataType: 'json',
			type: 'POST',
			data: data,
			success: function (res) {
				if (! res.success) {
					//console.log (res.error);
				} else {
					this.setState ({links: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				//console.error (this.props_add_url, status, err.toString ());
			}.bind (this)
		});
	},
	
	componentWillMount: function () {
		this.loadLinksFromServer ();
	},
	
	render: function () {
		return (
			<div className="link-box">
				<LinkList links={this.state.links} />
				<LinkForm onLinkSubmit={this.handleLinkSubmit} i18n={this.props.i18n} />
			</div>
		);
	}
});
