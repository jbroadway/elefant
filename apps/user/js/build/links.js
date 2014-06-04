/** @jsx React.DOM */
var Link = React.createClass ({displayName: 'Link',
	render: function () {
		var link = this.props.link;
		link.type = link.service.toLowerCase ().replace ('+', '');
		link.class_name = link.type + '-service';
		return (
			React.DOM.div( {className:"link"}, 
				React.DOM.span( {className:link.class_name}),
				React.DOM.a( {href:link.link}, link.link)
			)
		);
	}
});

var LinkList = React.createClass ({displayName: 'LinkList',
	render: function () {
		var links = [];
		console.log (this.props.links);
		this.props.links.forEach (function (link) {
			links.push (Link( {link:link, key:link.id} ));
		});
		return (React.DOM.div( {id:"link-list"}, links));
	}
});

var LinkForm = React.createClass ({displayName: 'LinkForm',
	render: function () {
		return (
			React.DOM.form( {onSubmit:this.handleSubmit}, 
				React.DOM.p(null, 
					React.DOM.input( {type:"text", ref:"handle", placeholder:this.props.i18n.link_placeholder, size:"35"} ),
					React.DOM.select( {ref:"service"}, 
						React.DOM.option( {value:""}, this.props.i18n.service),
						React.DOM.option( {value:"Facebook"}, "Facebook"),
						React.DOM.option( {value:"Google+"}, "Google+"),
						React.DOM.option( {value:"Instagram"}, "Instagram"),
						React.DOM.option( {value:"Twitter"}, "Twitter"),
						React.DOM.option( {value:"Tumblr"}, "Tumblr"),
						React.DOM.option( {value:"Website"}, "Website")
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
			React.DOM.div( {className:"link-box"}, 
				LinkList( {links:this.state.links} ),
				LinkForm( {onLinkSubmit:this.handleLinkSubmit, i18n:this.props.i18n} )
			)
		);
	}
});
