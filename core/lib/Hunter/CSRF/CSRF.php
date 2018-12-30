<?php

/**
 * @file
 *
 * CSRF
 */
 namespace Hunter\Core\CSRF;

 use Schnittstabil\Csrf\TokenService\TokenService;
 use \Lootils\Uuid\Uuid;

 class CSRF {

 	/**
 	 * The default token name
 	 */
 	const TOKEN_KEY = "_hunter_serect_token_645a83a41868941e4692aa31e7235f2";

	//generate key
	protected $generate_key;

	//tokenService
	protected $tokenService;

	public function __construct() {
			$this->generate_key = Uuid::createV4()->getUuid();
			// create the TokenService
			$this->tokenService = new TokenService(self::TOKEN_KEY);
	}

	/**
	 * Get a hidden input string with the token/token name in it.
	 *
	 * @param string $token_name - defaults to the default token name
	 * @return string
	 */
	public function getHiddenInputString()	{
		return sprintf('<input type="hidden" name="_csrf_token_uuid" id="_csrf_token_uuid" value="%s"/><input type="hidden" name="_csrf_token" id="_csrf_token" value="%s"/>', $this->generate_key, $this->getToken());
	}

	/**
	 * Get the token.  If it's not defined, this will go ahead and generate one.
	 *
	 * @param string $token_name - defaults to the default token name
	 * @return string
	 */
	public function getToken()	{
		// generate a URL-safe token, using the name of the authenticated user as nonce:
		$token = $this->tokenService->generate($this->generate_key);
		return $token;
	}

	/**
	 * Validate the token.  If there's not one yet, it will set one and return false.
	 *
	 * @param string $token_name - defaults to the default token name
	 * @return bool
	 */
	public function validate($token_uuid, $token)	{
		// validate the token - stateless; no session needed
	 	if (!$this->tokenService->validate($token_uuid, $token)) {
	 	  return false;
	 	}else {
	 		return true;
	 	}
	}
 }
