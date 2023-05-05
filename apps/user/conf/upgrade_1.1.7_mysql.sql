CREATE TABLE #prefix#oauth_clients (
	client_id             VARCHAR(80)   NOT NULL,
	client_secret         VARCHAR(80),
	redirect_uri          VARCHAR(2000),
	grant_types           VARCHAR(80),
	scope                 VARCHAR(4000),
	user_id               VARCHAR(80),
	client_name           VARCHAR(80)   NOT NULL DEFAULT '',
	PRIMARY KEY (client_id)
);

CREATE TABLE #prefix#oauth_access_tokens (
	access_token         VARCHAR(40)    NOT NULL,
	client_id            VARCHAR(80)    NOT NULL,
	user_id              VARCHAR(80),
	expires              TIMESTAMP      NOT NULL,
	scope                VARCHAR(4000),
	PRIMARY KEY (access_token)
);

CREATE TABLE #prefix#oauth_authorization_codes (
	authorization_code  VARCHAR(40)     NOT NULL,
	client_id           VARCHAR(80)     NOT NULL,
	user_id             VARCHAR(80),
	redirect_uri        VARCHAR(2000),
	expires             TIMESTAMP       NOT NULL,
	scope               VARCHAR(4000),
	id_token            VARCHAR(1000),
	PRIMARY KEY (authorization_code)
);

CREATE TABLE #prefix#oauth_refresh_tokens (
	refresh_token       VARCHAR(40)     NOT NULL,
	client_id           VARCHAR(80)     NOT NULL,
	user_id             VARCHAR(80),
	expires             TIMESTAMP       NOT NULL,
	scope               VARCHAR(4000),
	PRIMARY KEY (refresh_token)
);

CREATE TABLE #prefix#oauth_scopes (
	scope               VARCHAR(80)     NOT NULL,
	is_default          BOOLEAN,
	PRIMARY KEY (scope)
);

CREATE TABLE #prefix#oauth_jwt (
	client_id           VARCHAR(80)     NOT NULL,
	subject             VARCHAR(80),
	public_key          VARCHAR(2000)   NOT NULL
);

CREATE TABLE #prefix#oauth_jti (
	issuer              VARCHAR(80)   NOT NULL,
	subject             VARCHAR(80),
	audiance            VARCHAR(80),
	expires             TIMESTAMP     NOT NULL,
	jti                 VARCHAR(2000) NOT NULL
);

CREATE TABLE #prefix#oauth_public_keys (
	client_id            VARCHAR(80),
	public_key           VARCHAR(2000),
	private_key          VARCHAR(2000),
	encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
);
