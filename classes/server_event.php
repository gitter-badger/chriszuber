<?php
	class server_event extends json_response {
		/**
		 * Class to allow continuous updates from server using Server Sent Events
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Server-sent_events/Using_server-sent_events
		 * @usage:
		 * $event = new server_event(); $n = 42;
		 * while($n--) {
		 * 	$event->notify(
		 * 	'This is an example of a server event',
		 * 	'It functions the same has json_response, but can send multiple messages'
		 * )->html(
		 * 	'main',
		 * 	'This is the ' . 43 - $n .'th message'
		 * )->send()->wait(1)
		 * }
		 * $event->close();
		 */

		public function __construct(array $data = null) {
			/**
			 * Constructor for class. Class method to set headers
			 * and initialize first (optional) set of data.
			 *
			 * Inherits its methods from json_response, so do parent::__construct()
			 *
			 * @param array $data (optional array of data to be initialized with)
			 * @example $event = new server_event(['html' => ['main' => 'It Works!']]...)
			 */

			$this->set_headers();
			parent::__construct();

			if(is_array($data)) {
				$this->response = $data;
			}
		}

		public function send($key = null) {
			/**
			 * Sends everything with content-type of text/event-stream,
			 * Echos json_encode($this->response)
			 * An optional $key argument can be used to only
			 * send a subset of $this->response
			 *
			 * @param string $key
			 * @usage $event->send() or $event->send('notify')
			 */

			echo 'event: ping' . PHP_EOL;

			if(count($this->response)) {
				if(isset($key)) {
					echo 'data: ' . json_encode([$key => $this->response[$key]]) . PHP_EOL . PHP_EOL;
				}
				else {
					echo 'data: ' . json_encode($this->response) . PHP_EOL . PHP_EOL;
				}
				$this->response = [];
			}

			ob_flush();
			flush();
			return $this;
		}

		private function set_headers() {
			/**
			 * Sets headers required to be handled as a server event.
			 */

			header('Content-Type: text/event-stream');
			header_remove('X-Powered-By');
			header_remove('Expires');
			header_remove('Pragma');
			header_remove('X-Frame-Options');
			header_remove('Server');
			return $this;
		}

		public function wait($delay = 1) {
			/**
			 * Set delay between events and flush out
			 * previous response.
			 */

			sleep($delay);
			return $this;
		}

		public function close($key = null) {
			/**
			 * Same as the send() method, except this
			 * method indicates that it is the final event.
			 *
			 * The handler in handleJSON will terminate the serverEvent
			 * after receiving an event of type 'close'
			 *
			 * @param $key
			 * @usage $event->close() or $event->close('notify')
			 */

			echo 'event: close' . PHP_EOL;

			if(!empty($this->response)) {
				if(isset($key)) {
					echo 'data: ' . json_encode([$key => $this->response[$key]]) . PHP_EOL . PHP_EOL;
				}
				else {
					echo 'data: ' . json_encode($this->response) . PHP_EOL . PHP_EOL;
				}
				$this->response = [];
			}
			else {
				echo 'data: "{}"' . PHP_EOL . PHP_EOL;
			}

			ob_flush();
			flush();
			return $this;
		}
	}
?>