/** @jsx React.DOM */
var Link = React.createClass ({displayName: 'Link',
	render: function () {
		var link = this.props.link;
		link.type = link.service.toLowerCase ().replace ('+', '-plus');
		link.class_name = 'fa fa-' + link.type;
		if (link.type === 'website') {
			link.class_name = 'fa fa-external-link';
		}
		return (
			React.DOM.div( {className:"link"}, 
				React.DOM.span( {className:link.class_name}),
				React.DOM.a( {href:link.link, target:"_blank"}, link.link),
				" ",
				React.DOM.span( {className:"note-delete"}, 
					"(",React.DOM.a( {href:"#", onClick:this.handleDelete}, this.props.i18n.del),")"
				)
			)
		);
	},
	
	handleDelete: function (event) {
		event.preventDefault ();
		
		if (! confirm (this.props.i18n.confirm_delete_link)) {
			return false;
		}

		this.props.onLinkDelete ({id: this.props.link.id});
	}
});

var LinkList = React.createClass ({displayName: 'LinkList',
	render: function () {
		var links = [],
			i18n = this.props.i18n,
			link_delete = this.props.onLinkDelete;
		//console.log (this.props.links);
		this.props.links.forEach (function (link) {
			links.push (
				Link(
					{link:link,
					key:link.id,
					onLinkDelete:link_delete,
					i18n:i18n}
				)
			);
		});
		return (React.DOM.div( {id:"link-list"}, links));
	}
});

var LinkForm = React.createClass ({displayName: 'LinkForm',
	render: function () {
		return (
			React.DOM.form( {id:"social-link-form", onSubmit:this.handleSubmit}, 
				React.DOM.p(null, 
					React.DOM.input( {type:"text", ref:"handle", placeholder:this.props.i18n.link_placeholder, size:"35"} ),
					React.DOM.select( {ref:"service"}, 
						React.DOM.option( {value:""}, this.props.i18n.service),
						React.DOM.option( {value:"Facebook"}, "Facebook"),
						React.DOM.option( {value:"Google+"}, "Google+"),
						React.DOM.option( {value:"Instagram"}, "Instagram"),
						React.DOM.option( {value:"Twitter"}, "Twitter"),
						React.DOM.option( {value:"Tumblr"}, "Tumblr"),
						React.DOM.option( {value:"Website"}, "Website"),
						React.DOM.option( {value:"YouTube"}, "YouTube")
					),
					React.DOM.input( {type:"submit", value:this.props.i18n.add_link} )
				)
			)
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

var LinkBox = React.createClass ({displayName: 'LinkBox',
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
	
	handleLinkDelete: function (data) {
		var notification = this.props.i18n.link_deleted;

		data.user = this.props.user_id;
		//console.log (data);
		$.ajax ({
			url: this.props.del_url,
			dataType: 'json',
			type: 'POST',
			data: data,
			success: function (res) {
				if (! res.success) {
					//console.log (res.error);
				} else {
					$.add_notification (notification);
					this.setState ({links: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				//console.error (this.props_add_url, status, err.toString ());
			}.bind (this)
		});
	},
	
	handleLinkSubmit: function (data) {
		var notification = this.props.i18n.link_added;

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
					$.add_notification (notification);
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
			React.DOM.div( {className:"link-box"}, 
				LinkList(
					{links:this.state.links,
					onLinkDelete:this.handleLinkDelete,
					i18n:this.props.i18n}
				),
				LinkForm(
					{onLinkSubmit:this.handleLinkSubmit,
					i18n:this.props.i18n}
				)
			)
		);
	}
});
