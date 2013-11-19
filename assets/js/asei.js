/**
 * ASEI CS
 * Application Server Event Interface - Client Side
 * Version 0.1.5
 * 
 * Copyright 2013 Geoffrey L. Kuhl
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Notice:
 * ASEI does not support legacy browsers such as Internet Explorer.
 * Currently, Internet Explorer 10 does not support SSE technology
 * so working configurations are currently limited to Firefox, Chromium,
 * Opera, Safari and any other web-standard compliant browser.
 *
 * Requires jQuery 2.0.3
 *
 *@todo  Error Handling at every level in each function
 *
 *@author Geoffrey L. Kuhl
 *@package asei
 *@subpackage client-side
 *@version 0.1.5
 *
 *@param String source The source can be a relative or absolute url of the server respondent.
 *                     the source is optional and when not passed will default to a relative 
 *                     path of ./asei.php which may require some url re-writing. It is
 *                     perferred to always supply the source url.
 *
 */
function asei(source) {

	/**
	 * Server source URL of asei connector
	 * @type String
	 */
	this.source		= source;

	/**
	 * Server Connection Object
	 * @type Object
	 */
	this.connection	= {};

	/**
	 * Connection State
	 * @type Boolean
	 */
	this.connected	= false;

	/**
	 * Validate Source
	 * @type String
	 */
	if (typeof this.source === "undefined") this.source = 'request/';



	/**
	 * Connect
	 * Establish connection to Sever
	 * @return Object Self
	 */
	this.connect 	= function() {

		/**
		 * If window.EventSource is supported then
		 * instansiate the connection. If window.EventSource
		 * is not supported then do not attempt to handle, 
		 * just show an error message and exit
		 */
		if (!!window.EventSource) {
			this.connection = new EventSource(this.source);
			this.connected	= true;
		} else {
			this.ieError();
			this.connected	= false;
			return false;
		}

		/** Handle Error Event **/
		this.connection.addEventListener('error', this.disconnect);

		/** Handle Close Request **/
		this.connection.addEventListener('close', this.disconnect);


		/* Return Self */
		return this;
	}



	/**
	 * Disconnect
	 * Disconnect from Server
	 * @return Object Self
	 */
	this.disconnect	= function() {

		/* Check Object before attempting disconnect */
		if (typeof this.connection === "undefined") {
			this.connected = false;
			return this;
		}

		/* Terminate Connection to Server */
		this.connection.close();
		this.connected = false;

		/* Return Self */
		return this;
	}



	/**
	 * Listen For
	 * Register server-side event / client-side handler
	 * @param  String	eventName Name of the event to listen for
	 * @param  Function callback Name of callback function
	 * @return Object 	Self
	 */
	this.listenFor	= function(eventName, callback) {

		/**
		 * If not connected, attempt to connect
		 */
		if (!this.connected) this.connect();

		/**
		 * Add Callback Event Listener
		 */
		this.connection.addEventListener(eventName, function(event) {
			var json = JSON.parse(event.data);
			callback(json.result);
		}, false);

		/* Return Self */
		return this;
	}



	/**
	 * Request From
	 * Send xmlHTTP POST request
	 *
	 * @param  JSON 	data
	 * @param  Function callback
	 * @return Object 	Self
	 */
	this.update	= function(data, callback) {

		/** jQuery POST Request **/
		$.post(this.source, data, function(data) {
			callback(data);
		}, "json");

		/* Return Self */
		return this;
	}



	this.create			= function(data, callback) {
		/** jQuery ajax Request **/
		$.ajax({
			url: this.source,
			type: 'PUT',
			data: data,
			success: function(data) {
				var parsed = JSON.parse(data);
				callback(parsed);
			}
		});

		/* Return Self */
		return this;	
	}



	this.get			= function(data, callback) {
		/** jQuery ajax Request **/
		$.ajax({
			url: this.source,
			type: 'GET',
			data: data,
			success: function(data) {
				callback(data);
			}
		});

		/* Return Self */
		return this;	
	}


	this.page 			= function(page, element) {
		/** jQuery ajax Request **/
		$.ajax({
			url: page + '/',
			type: 'GET',
			data: {},
			success: function(data) {
				$(element)
					.fadeOut(500, function() {
						$(this)
							.html(data)
							.fadeIn(500)
						;
					});
				;
			}
		});

		/* Return Self */
		return this;
	}


	this.delete			= function(data, callback) {
		/** jQuery ajax Request **/
		$.ajax({
			url: this.source,
			type: 'DELETE',
			data: data,
			success: callback
		});

		/* Return Self */
		return this;		
	}



	/**
	 * Internet Explorer Error
	 * ieError prints out an error to the end-user to
	 * get a good browser.
	 * @return Object Self
	 */
	this.ieError 	= function() {
		window.onload	= function() {
			document.body.innerHTML = 
				"<h4>Your Browser does not support SSE and this page will not continue</h4>" +
				"<p>Please use a Web-Standards Browser Like Firefox, Chrome, Opera, or Safari</p>" +
				"<hr/>"
			;
		}

		/* Return Self */
		return this;
	}



	/**
	 *@return Object Self
	 */
	return this;
}


/**
 * onServer and _
 * Shorthand for ASEI
 * 
 * @type Object		onServer	A ready-to-go object connecting to ./request/
 * @type Function 	_ 			Shorthand for asei()
 */
var onServer	= new asei();
var _			= asei;
