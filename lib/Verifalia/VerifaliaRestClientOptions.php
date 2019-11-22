<?php

namespace Verifalia {

	class VerifaliaRestClientOptions
	{
		/**
		 * username: (string) The username of the Verifalia user to authenticate with. While authenticating with your
		 * Verifalia main account credentials is possible, it is strongly advised to create one or more users (formerly
		 * known as sub-accounts) with just the required permissions, for improved security. To create a new user or
		 * manage existing ones, please visit https://verifalia.com/client-area#/users
		 */
		const USERNAME = 'username';

		/**
		 * password: (string) The password of the Verifalia user to authenticate with. While authenticating with your
		 * Verifalia main account credentials is possible, it is strongly advised to create one or more users (formerly
		 * known as sub-accounts) with just the required permissions, for improved security. To create a new user or
		 * manage existing ones, please visit https://verifalia.com/client-area#/users
		 */
		const PASSWORD = 'password';

		/**
		 * baseUris: (string[]) The base URIs of the Verifalia API - please do *NOT* set these unless you have been
		 * instructed to do so by the Verifalia support team.
		 */
		const BASE_URIS = 'baseUris';
	}
}
