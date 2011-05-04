# bands/organizations
create table org (
	id int not null auto_increment primary key,
	user_id int not null,
	name char(72) not null,
	website char(128) not null,
	event_feed char(128) not null,
	info text not null,
	index (user_id)
);

# shows by organization
create table event (
	id int not null auto_increment primary key,
	org_id int not null,
	ts datetime not null,
	city char(48) not null,
	state char(3) not null,
	country char(3) not null,
	venue char(72) not null,
	info text not null,
	link char(128) not null,
	feed_id char(128) not null,
	index (org_id, feed_id),
	index (org_id, ts),
	index (country, state, city)
);

# for finding events by location
create table geocache (
	city char(48) not null,
	state char(3) not null,
	country char(3) not null,
	lat float not null,
	lng float not null,
	index (city, state, country),
	index (lat, lng)
);

# for reserving tickets during purchase process
create table temp_ticket (
	id int not null auto_increment primary key,
	event_id int not null,
	type_id int not null,
	user_id int not null,
	created datetime not null,
	index (user_id),
	index (event_id, created)
);

# actual tickets sold
create table ticket (
	id int not null auto_increment primary key,
	hash char(64) not null,
	event_id int not null,
	type_id int not null,
	user_id int not null,
	purchased datetime not null,
	scanned enum('no','yes') not null default 'no',
	index (hash),
	index (event_id),
	index (user_id)
);

# extra details for each user
create table userdetails (
	user_id int not null primary key,
	paypal_id char(72) not null
);
