<?php

namespace user\Auth\OAuth\Storage;

use OAuth2\Storage\AuthorizationCodeInterface;
use OAuth2\Storage\AccessTokenInterface;
use OAuth2\Storage\ClientCredentialsInterface;
use OAuth2\OpenID\Storage\UserClaimsInterface;
use OAuth2\Storage\UserCredentialsInterface;
use OAuth2\Storage\RefreshTokenInterface;
use OAuth2\Storage\JwtBearerInterface;
use OAuth2\Storage\ScopeInterface;
use OAuth2\Storage\PublicKeyInterface;
use OAuth2\OpenID\Storage\AuthorizationCodeInterface as OpenIDAuthorizationCodeInterface;
use InvalidArgumentException;
use DB;
use User;
use Appconf;

/**
 * Simple DB storage for all storage types
 */
class DBStorage implements
    AuthorizationCodeInterface,
    AccessTokenInterface,
    ClientCredentialsInterface,
    UserCredentialsInterface,
    RefreshTokenInterface,
    JwtBearerInterface,
    ScopeInterface,
    PublicKeyInterface,
    UserClaimsInterface,
    OpenIDAuthorizationCodeInterface
{

    /**
     * @var array
     */
    protected $config;

    /**
     * @param mixed $connection
     *
     * @throws InvalidArgumentException
     */
    public function __construct () {
        $this->config = [
            'client_table'          => '#prefix#oauth_clients',
            'access_token_table'    => '#prefix#oauth_access_tokens',
            'refresh_token_table'   => '#prefix#oauth_refresh_tokens',
            'code_table'            => '#prefix#oauth_authorization_codes',
            'user_table'            => '#prefix#user',
            'jwt_table'             => '#prefix#oauth_jwt',
            'jti_table'             => '#prefix#oauth_jti',
            'scope_table'           => '#prefix#oauth_scopes',
            'public_key_table'      => '#prefix#oauth_public_keys',
        ];
    }

    /**
     * @param string $client_id
     * @param null|string $client_secret
     * @return bool
     */
    public function checkClientCredentials ($client_id, $client_secret = null) {
        $result = DB::single_array (sprintf ('SELECT * from %s where client_id = ?', $this->config['client_table']), compact ('client_id'));

        // make this extensible
        return $result && $result['client_secret'] == $client_secret;
    }

    /**
     * @param string $client_id
     * @return bool
     */
    public function isPublicClient ($client_id) {
        $result = DB::single_array (sprintf ('SELECT * from %s where client_id = ?', $this->config['client_table']), compact ('client_id'));

        if (! $result) {
            return false;
        }

        return empty ($result['client_secret']);
    }

    /**
     * @param string $client_id
     * @return array|mixed
     */
    public function getClientDetails ($client_id) {
        return DB::single_array (sprintf ('SELECT * from %s where client_id = ?', $this->config['client_table']), compact ('client_id'));
    }

    /**
     * @param string $client_id
     * @param null|string $client_secret
     * @param null|string $redirect_uri
     * @param null|array  $grant_types
     * @param null|string $scope
     * @param null|string $user_id
     * @return bool
     */
    public function setClientDetails ($client_id, $client_secret = null, $redirect_uri = null, $grant_types = null, $scope = null, $user_id = null) {
        // if it exists, update it.
        if ($this->getClientDetails ($client_id)) {
            return DB::execute (
                sprintf ('UPDATE %s SET client_secret=?, redirect_uri=?, grant_types=?, scope=?, user_id=? where client_id=?', $this->config['client_table']),
                compact ('client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id', 'client_id')
            );
        }
        
        return DB::execute (
            sprintf ('INSERT INTO %s (client_id, client_secret, redirect_uri, grant_types, scope, user_id) VALUES (?, ?, ?, ?, ?, ?)', $this->config['client_table']),
            compact ('client_id', 'client_secret', 'redirect_uri', 'grant_types', 'scope', 'user_id')
        );
    }

    /**
     * @param $client_id
     * @param $grant_type
     * @return bool
     */
    public function checkRestrictedGrantType ($client_id, $grant_type) {
        $details = $this->getClientDetails ($client_id);
        if (isset ($details['grant_types']) && $details['grant_types'] != '') {
            $grant_types = explode (' ', $details['grant_types']);

            return in_array ($grant_type, (array) $grant_types);
        }

        // if grant_types are not defined, then none are restricted
        return true;
    }

    /**
     * @param string $access_token
     * @return array|bool|mixed|null
     */
    public function getAccessToken ($access_token) {
        $token = DB::single_array (sprintf ('SELECT * from %s where access_token = ?', $this->config['access_token_table']), compact ('access_token'));

        if ($token) {
            // convert date string back to timestamp
            $token['expires'] = strtotime ($token['expires']);
        }

        return $token;
    }

    /**
     * @param string $access_token
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param int    $expires
     * @param string $scope
     * @return bool
     */
    public function setAccessToken ($access_token, $client_id, $user_id, $expires, $scope = null) {
        // convert expires to datestring
        $expires = date ('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAccessToken ($access_token)) {
            return DB::execute (
                sprintf ('UPDATE %s SET client_id=?, expires=?, user_id=?, scope=? where access_token=?', $this->config['access_token_table']),
                compact ('client_id', 'expires', 'user_id', 'scope', 'access_token')
            );
        }
        
        return DB::execute (
            sprintf ('INSERT INTO %s (access_token, client_id, expires, user_id, scope) VALUES (?, ?, ?, ?, ?)', $this->config['access_token_table']),
            compact ('access_token', 'client_id', 'expires', 'user_id', 'scope')
        );
    }

    /**
     * @param $access_token
     * @return bool
     */
    public function unsetAccessToken ($access_token) {
        DB::execute (sprintf ('DELETE FROM %s WHERE access_token = ?', $this->config['access_token_table']), compact ('access_token'));

        return DB::execute_count () > 0;
    }

    /* OAuth2\Storage\AuthorizationCodeInterface */
    /**
     * @param string $code
     * @return mixed
     */
    public function getAuthorizationCode ($code) {
        $code = DB::single_array (sprintf ('SELECT * from %s where authorization_code = :code', $this->config['code_table']), compact('code'));

        if ($code) {
            // convert date string back to timestamp
            $code['expires'] = strtotime ($code['expires']);
        }

        return $code;
    }

    /**
     * @param string $code
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $redirect_uri
     * @param int    $expires
     * @param string $scope
     * @param string $id_token
     * @return bool|mixed
     */
    public function setAuthorizationCode ($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null) {
        if (func_num_args() > 6) {
            // we are calling with an id token
            return call_user_func_array ([$this, 'setAuthorizationCodeWithIdToken'], func_get_args ());
        }

        // convert expires to datestring
        $expires = date ('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAuthorizationCode ($code)) {
            return DB::execute (
                sprintf ('UPDATE %s SET client_id=?, user_id=?, redirect_uri=?, expires=?, scope=? where authorization_code=?', $this->config['code_table']),
                compact ('client_id', 'user_id', 'redirect_uri', 'expires', 'scope', 'code')
            );
        }

        return DB::execute (
            sprintf ('INSERT INTO %s (authorization_code, client_id, user_id, redirect_uri, expires, scope) VALUES (?, ?, ?, ?, ?, ?)', $this->config['code_table']),
            compact ('code', 'client_id', 'user_id', 'redirect_uri', 'expires', 'scope')
        );
    }

    /**
     * @param string $code
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $redirect_uri
     * @param string $expires
     * @param string $scope
     * @param string $id_token
     * @return bool
     */
    private function setAuthorizationCodeWithIdToken ($code, $client_id, $user_id, $redirect_uri, $expires, $scope = null, $id_token = null) {
        // convert expires to datestring
        $expires = date ('Y-m-d H:i:s', $expires);

        // if it exists, update it.
        if ($this->getAuthorizationCode ($code)) {
            return DB::execute (
                sprintf ('UPDATE %s SET client_id=?, user_id=?, redirect_uri=?, expires=?, scope=?, id_token =? where authorization_code=?', $this->config['code_table']),
                compact ('client_id', 'user_id', 'redirect_uri', 'expires', 'scope', 'id_token', 'code')
            );
        }

        return DB::execute (
            sprintf ('INSERT INTO %s (authorization_code, client_id, user_id, redirect_uri, expires, scope, id_token) VALUES (?, ?, ?, ?, ?, ?, ?)', $this->config['code_table']),
            compact ('code', 'client_id', 'user_id', 'redirect_uri', 'expires', 'scope', 'id_token')
        );
    }

    /**
     * @param string $code
     * @return bool
     */
    public function expireAuthorizationCode ($code) {
        return DB::execute (sprintf ('DELETE FROM %s WHERE authorization_code = ?', $this->config['code_table']), compact ('code'));
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function checkUserCredentials ($username, $password) {
        if ($user = $this->getUser ($username)) {
            return $this->checkPassword ($user, $password);
        }

        return false;
    }

    /**
     * @param string $username
     * @return array|bool
     */
    public function getUserDetails ($username) {
        return $this->getUser ($username);
    }

    /**
     * @param mixed  $user_id
     * @param string $claims
     * @return array|bool
     */
    public function getUserClaims ($user_id, $claims) {
        if (! $userDetails = $this->getUserDetails ($user_id)) {
            return false;
        }

        $claims = explode (' ', trim ($claims));
        $userClaims = [];

        // for each requested claim, if the user has the claim, set it in the response
        $validClaims = explode (' ', self::VALID_CLAIMS);
        foreach ($validClaims as $validClaim) {
            if (in_array ($validClaim, $claims)) {
                if ($validClaim == 'address') {
                    // address is an object with subfields
                    $userClaims['address'] = $this->getUserClaim ($validClaim, $userDetails['address'] ?: $userDetails);
                } else {
                    $userClaims = array_merge ($userClaims, $this->getUserClaim ($validClaim, $userDetails));
                }
            }
        }

        return $userClaims;
    }

    /**
     * @param string $claim
     * @param array  $userDetails
     * @return array
     */
    protected function getUserClaim ($claim, $userDetails) {
        $userClaims = [];
        $claimValuesString = constant (sprintf ('self::%s_CLAIM_VALUES', strtoupper ($claim)));
        $claimValues = explode (' ', $claimValuesString);

        foreach ($claimValues as $value) {
            $userClaims[$value] = isset ($userDetails[$value]) ? $userDetails[$value] : null;
        }

        return $userClaims;
    }

    /**
     * @param string $refresh_token
     * @return bool|mixed
     */
    public function getRefreshToken ($refresh_token) {
        $token = DB::single_array (sprintf ('SELECT * FROM %s WHERE refresh_token = ?', $this->config['refresh_token_table']), compact ('refresh_token'));

        if ($token) {
            // convert expires to epoch time
            $token['expires'] = strtotime ($token['expires']);
        }

        return $token;
    }

    /**
     * @param string $refresh_token
     * @param mixed  $client_id
     * @param mixed  $user_id
     * @param string $expires
     * @param string $scope
     * @return bool
     */
    public function setRefreshToken ($refresh_token, $client_id, $user_id, $expires, $scope = null) {
        // convert expires to datestring
        $expires = date ('Y-m-d H:i:s', $expires);

        return DB::execute (
            sprintf ('INSERT INTO %s (refresh_token, client_id, user_id, expires, scope) VALUES (?, ?, ?, ?, ?)', $this->config['refresh_token_table']),
            compact('refresh_token', 'client_id', 'user_id', 'expires', 'scope')
        );
    }

    /**
     * @param string $refresh_token
     * @return bool
     */
    public function unsetRefreshToken ($refresh_token) {
        DB::execute (sprintf ('DELETE FROM %s WHERE refresh_token = ?', $this->config['refresh_token_table']), compact ('refresh_token'));

        return DB::execute_count () > 0;
    }

    /**
     * plaintext passwords are bad!  Override this for your application
     *
     * @param array $user
     * @param string $password
     * @return bool
     */
    protected function checkPassword ($user, $password) {
        return hash_equals (crypt ($password, $user['password']), $user['password']);
    }

    // use a secure hashing algorithm when storing passwords. Override this for your application
    protected function hashPassword ($password) {
        return User::encrypt_pass ($password);
    }

    /**
     * @param string $username
     * @return array|bool
     */
    public function getUser ($username) {
        $userInfo = DB::single_array (sprintf ('SELECT * from %s where email=?', $this->config['user_table']), compact ('username'));

        if (! $userInfo) {
            return false;
        }

        return array_merge ([
            'user_id' => $userInfo['id']
        ], $userInfo);
    }

    /**
     * plaintext passwords are bad!  Override this for your application
     *
     * @param string $username
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @return bool
     */
    public function setUser ($username, $password, $firstName = null, $lastName = null) {
        // do not store in plaintext
        $password = $this->hashPassword ($password);

        // if it exists, update it.
        if ($this->getUser ($username)) {
            return DB::execute (
                sprintf ('UPDATE %s SET password=?, name=? where email=?', $this->config['user_table']),
                [$password, $firstName . ' ' . $lastName, $username]
            );
        }
        
        return DB::execute (
            sprintf ('INSERT INTO %s (email, password, name, type, signed_up, updated, userdata, about) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', $this->config['user_table']),
            [
                $username,
                $password,
                $firstName . ' ' . $lastName,
                Appconf::user ('User', 'default_role'),
                gmdate ('Y-m-d H:i:s'),
                gmdate ('Y-m-d H:i:s'),
                '[]',
                ''
            ]
        );
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function scopeExists ($scope) {
        $scope = explode (' ', $scope);
        $whereIn = implode (',', array_fill (0, count($scope), '?'));

        $result = DB::single_array (sprintf ('SELECT count(scope) as count FROM %s WHERE scope IN (%s)', $this->config['scope_table'], $whereIn), $scope);

        if ($result) {
            return $result['count'] == count ($scope);
        }

        return false;
    }

    /**
     * @param mixed $client_id
     * @return null|string
     */
    public function getDefaultScope ($client_id = null) {
        $result = DB::single_array (sprintf ('SELECT scope FROM %s WHERE is_default=?', $this->config['scope_table']), true);

        if ($result) {
            $defaultScope = array_map (function ($row) {
                return $row['scope'];
            }, $result);

            return implode (' ', $defaultScope);
        }

        return null;
    }

    /**
     * @param mixed $client_id
     * @param $subject
     * @return string
     */
    public function getClientKey ($client_id, $subject) {
        return DB::shift_array (
            sprintf ('SELECT public_key from %s where client_id=? AND subject=?', $this->config['jwt_table']),
            [$client_id, $subject]
        );
    }

    /**
     * @param mixed $client_id
     * @return bool|null
     */
    public function getClientScope ($client_id) {
        if (! $clientDetails = $this->getClientDetails ($client_id)) {
            return false;
        }

        if (isset ($clientDetails['scope'])) {
            return $clientDetails['scope'];
        }

        return null;
    }

    /**
     * @param mixed $client_id
     * @param $subject
     * @param $audience
     * @param $expires
     * @param $jti
     * @return array|null
     */
    public function getJti ($client_id, $subject, $audience, $expires, $jti) {
        $result = DB::single_array (
            sprintf ('SELECT * FROM %s WHERE issuer=? AND subject=? AND audience=? AND expires=? AND jti=?', $this->config['jti_table']),
            compact ('client_id', 'subject', 'audience', 'expires', 'jti')
        );

        if ($result) {
            return [
                'issuer' => $result['issuer'],
                'subject' => $result['subject'],
                'audience' => $result['audience'],
                'expires' => $result['expires'],
                'jti' => $result['jti'],
            ];
        }

        return null;
    }

    /**
     * @param mixed $client_id
     * @param $subject
     * @param $audience
     * @param $expires
     * @param $jti
     * @return bool
     */
    public function setJti ($client_id, $subject, $audience, $expires, $jti) {
        return DB::execute (
            sprintf ('INSERT INTO %s (issuer, subject, audience, expires, jti) VALUES (?, ?, ?, ?, ?)', $this->config['jti_table']),
            compact ('client_id', 'subject', 'audience', 'expires', 'jti')
        );
    }

    /**
     * @param mixed $client_id
     * @return mixed
     */
    public function getPublicKey ($client_id = null) {
        $result = DB::single_array (sprintf ('SELECT public_key FROM %s WHERE client_id=:client_id OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']), compact ('client_id'));

        if ($result) {
            return $result['public_key'];
        }
    }

    /**
     * @param mixed $client_id
     * @return mixed
     */
    public function getPrivateKey ($client_id = null) {
        $result = DB::single_array (sprintf ('SELECT private_key FROM %s WHERE client_id=? OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']), compact ('client_id'));

        if ($result) {
            return $result['private_key'];
        }
    }

    /**
     * @param mixed $client_id
     * @return string
     */
    public function getEncryptionAlgorithm ($client_id = null) {
        $result = DB::single_array (sprintf ('SELECT encryption_algorithm FROM %s WHERE client_id=? OR client_id IS NULL ORDER BY client_id IS NOT NULL DESC', $this->config['public_key_table']), compact ('client_id'));

        if ($result) {
            return $result['encryption_algorithm'];
        }

        return 'RS256';
    }

    /**
     * DDL to create OAuth2 database and tables for PDO storage
     *
     * @see https://github.com/dsquier/oauth2-server-php-mysql
     *
     * @param string $dbName
     * @return string
     */
    public function getBuildSql ($dbName = 'oauth2_server_php') {
        $sql = "
        CREATE TABLE {$this->config['client_table']} (
          client_id             VARCHAR(80)   NOT NULL,
          client_secret         VARCHAR(80),
          redirect_uri          VARCHAR(2000),
          grant_types           VARCHAR(80),
          scope                 VARCHAR(4000),
          user_id               VARCHAR(80),
          PRIMARY KEY (client_id)
        );

            CREATE TABLE {$this->config['access_token_table']} (
              access_token         VARCHAR(40)    NOT NULL,
              client_id            VARCHAR(80)    NOT NULL,
              user_id              VARCHAR(80),
              expires              TIMESTAMP      NOT NULL,
              scope                VARCHAR(4000),
              PRIMARY KEY (access_token)
            );

            CREATE TABLE {$this->config['code_table']} (
              authorization_code  VARCHAR(40)    NOT NULL,
              client_id           VARCHAR(80)    NOT NULL,
              user_id             VARCHAR(80),
              redirect_uri        VARCHAR(2000),
              expires             TIMESTAMP      NOT NULL,
              scope               VARCHAR(4000),
              id_token            VARCHAR(1000),
              PRIMARY KEY (authorization_code)
            );

            CREATE TABLE {$this->config['refresh_token_table']} (
              refresh_token       VARCHAR(40)    NOT NULL,
              client_id           VARCHAR(80)    NOT NULL,
              user_id             VARCHAR(80),
              expires             TIMESTAMP      NOT NULL,
              scope               VARCHAR(4000),
              PRIMARY KEY (refresh_token)
            );

            CREATE TABLE {$this->config['user_table']} (
              username            VARCHAR(80),
              password            VARCHAR(80),
              first_name          VARCHAR(80),
              last_name           VARCHAR(80),
              email               VARCHAR(80),
              email_verified      BOOLEAN,
              scope               VARCHAR(4000)
            );

            CREATE TABLE {$this->config['scope_table']} (
              scope               VARCHAR(80)  NOT NULL,
              is_default          BOOLEAN,
              PRIMARY KEY (scope)
            );

            CREATE TABLE {$this->config['jwt_table']} (
              client_id           VARCHAR(80)   NOT NULL,
              subject             VARCHAR(80),
              public_key          VARCHAR(2000) NOT NULL
            );

            CREATE TABLE {$this->config['jti_table']} (
              issuer              VARCHAR(80)   NOT NULL,
              subject             VARCHAR(80),
              audiance            VARCHAR(80),
              expires             TIMESTAMP     NOT NULL,
              jti                 VARCHAR(2000) NOT NULL
            );

            CREATE TABLE {$this->config['public_key_table']} (
              client_id            VARCHAR(80),
              public_key           VARCHAR(2000),
              private_key          VARCHAR(2000),
              encryption_algorithm VARCHAR(100) DEFAULT 'RS256'
            )
        ";

        return $sql;
    }
}