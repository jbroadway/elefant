/** @jsx React.DOM */
var Note = React.createClass ({
	render: function () {
		var note = this.props.note,
			del = '';

		if (this.props.current_user == note.made_by) {
			del = (
				<span className="note-delete">
					(<a href="#" onClick={this.handleDelete}>{this.props.i18n.del}</a>)
				</span>
			);
		}

		return (
			<div className="note">
				<div className="note-info-line">
					<span className="note-date-time" dangerouslySetInnerHTML={{__html: note.date}} />
					&nbsp;&mdash;&nbsp;
					<span className="note-made-by">{note.made_by_name}</span>
					&nbsp;
					{del}
				</div>
				<span className="note-body">{note.note}</span>
			</div>
		);
	},
	
	handleDelete: function (event) {
		event.preventDefault ();
		
		if (! confirm (this.props.i18n.confirm_delete_note)) {
			return false;
		}

		this.props.onNoteDelete ({id: this.props.note.id});
	}
});

var NoteList = React.createClass ({
	render: function () {
		var notes = [],
			i18n = this.props.i18n,
			user_id = this.props.user_id,
			note_delete = this.props.onNoteDelete,
			current_user = this.props.current_user;

		this.props.notes.forEach (function (note) {
			notes.push (
				<Note
					note={note}
					key={note.id}
					user_id={user_id}
					current_user={current_user}
					onNoteDelete={note_delete}
					i18n={i18n}
				/>
			);
		});
		return (<div id="note-list">{notes}</div>);
	}
});

var NoteForm = React.createClass ({
	render: function () {
		return (
			<form onSubmit={this.handleSubmit}>
				<p>
					<textarea ref="note" cols="70" rows="4"></textarea><br />
					<input type="submit" value={this.props.i18n.add_note} />
				</p>
			</form>
		);
	},
	
	handleSubmit: function (event) {
		event.preventDefault ();
		var note = this.refs.note.getDOMNode ().value;
		this.props.onNoteSubmit ({note: note});
		this.refs.note.getDOMNode ().value = '';
	}
});

var NoteBox = React.createClass ({
	getInitialState: function () {
		return {notes: []};
	},
	
	loadNotesFromServer: function () {
		$.ajax ({
			url: this.props.init_url,
			dataType: 'json',
			success: function (res) {
				if (! res.success) {
					//console.log (res.error);
				} else {
					this.setState ({notes: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				//console.log (err.toString ());
			}.bind (this)
		});
	},
	
	handleNoteDelete: function (data) {
		var notification = this.props.i18n.note_deleted;

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
					this.setState ({notes: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				//console.error (this.props_add_url, status, err.toString ());
			}.bind (this)
		});
	},
	
	handleNoteSubmit: function (data) {
		var notification = this.props.i18n.note_added;

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
					this.setState ({notes: res.data});
				}
			}.bind (this),
			error: function (xhr, status, err) {
				// do nothing
				//console.error (this.props_add_url, status, err.toString ());
			}.bind (this)
		});
	},
	
	componentWillMount: function () {
		this.loadNotesFromServer ();
	},

	render: function () {
		return (
			<div className="note-box">
				<NoteList
					notes={this.state.notes}
					user_id={this.props.user_id}
					current_user={this.props.current_user}
					onNoteDelete={this.handleNoteDelete}
					i18n={this.props.i18n}
				/>
				<NoteForm
					onNoteSubmit={this.handleNoteSubmit}
					i18n={this.props.i18n}
				/>
			</div>
		);
	}
});
