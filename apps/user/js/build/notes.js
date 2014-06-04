/** @jsx React.DOM */
var Note = React.createClass ({displayName: 'Note',
	render: function () {
		var note = this.props.note;
		return (
			React.DOM.div( {className:"note"}, 
				React.DOM.div( {className:"note-info-line"}, 
					React.DOM.span( {className:"note-date-time", dangerouslySetInnerHTML:{__html: note.date}} ),
					" — ",
					React.DOM.span( {className:"note-made-by"}, note.made_by_name)
				),
				React.DOM.span( {className:"note-body"}, note.note)
			)
		);
	}
});

var NoteList = React.createClass ({displayName: 'NoteList',
	render: function () {
		var notes = [];
		this.props.notes.forEach (function (note) {
			notes.push (Note( {note:note, key:note.id} ));
		});
		return (React.DOM.div( {id:"note-list"}, notes));
	}
});

var NoteForm = React.createClass ({displayName: 'NoteForm',
	render: function () {
		return (
			React.DOM.form( {onSubmit:this.handleSubmit}, 
				React.DOM.p(null, 
					React.DOM.textarea( {ref:"note", cols:"70", rows:"4"}),React.DOM.br(null ),
					React.DOM.input( {type:"submit", value:this.props.i18n.add_note} )
				)
			)
		);
	},
	
	handleSubmit: function (event) {
		event.preventDefault ();
		var note = this.refs.note.getDOMNode ().value;
		this.props.onNoteSubmit ({note: note});
		this.refs.note.getDOMNode ().value = '';
	}
});

var NoteBox = React.createClass ({displayName: 'NoteBox',
	getInitialState: function () {
		return {notes: []};
	},
	
	loadNotesFromServer: function () {
		$.ajax ({
			url: this.props.init_url,
			dataType: 'json',
			success: function (res) {
				if (! res.success) {
					console.log (res.error);
				} else {
					this.setState ({notes: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				console.log (err.toString ());
			}.bind (this)
		});
	},
	
	handleNoteSubmit: function (data) {
		data.user = this.props.user_id;
		console.log (data);
		$.ajax ({
			url: this.props.add_url,
			dataType: 'json',
			type: 'POST',
			data: data,
			success: function (res) {
				if (! res.success) {
					console.log (res.error);
				} else {
					this.setState ({notes: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				console.error (this.props_add_url, status, err.toString ());
			}.bind (this)
		});
	},
	
	componentWillMount: function () {
		this.loadNotesFromServer ();
	},

	render: function () {
		return (
			React.DOM.div( {className:"note-box"}, 
				NoteList( {notes:this.state.notes} ),
				NoteForm( {onNoteSubmit:this.handleNoteSubmit, i18n:this.props.i18n} )
			)
		);
	}
});
