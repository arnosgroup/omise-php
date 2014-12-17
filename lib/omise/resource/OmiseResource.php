<?php
require_once dirname(__FILE__).'/OmiseObject.php';

class OmiseResource extends OmiseObject {
	// リクエストメソッドたち
	const REQUEST_GET = 'GET';
	const REQUEST_POST = 'POST';
	const REQUEST_DELETE = 'DELETE';
	const REQUEST_PATCH = 'PATCH';
	
	// アクセス先
	const MODE_API = 0x01;
	const MODE_VAULT = 0x02;
	
	// OmiseのベースURL
	const _resourceURL = array('https://api.omise.co/', 'https://vault.omise.co/');
	protected $_mode;
	protected $_endpoint = '';
	
	protected function get() {
		return $this->execute(self::URLBASE_API, self::REQUEST_GET, $key)
	}
	protected function patch() {
		
	}
	protected function post() {
		
	}
	protected function delete() {
		
	}
	
	/**
	 * 戻り値は連想配列にされたjsonオブジェクト（ヘッダは含まない）
	 * @param string $url
	 * @param string $requestMethod
	 * @param array $params
	 * @throws OmiseException
	 * @return string
	 */
	private function execute($url, $requestMethod, $key, $params = null) {
		$ch = curl_init($url);
		curl_setopt_array($ch, $this->genOptions($requestMethod, $key.':', $params));
		// リクエストを実行し、失敗した場合には例外を投げる
		if(($result = curl_exec($ch)) === false) {
			$error = curl_error($ch);
			curl_close($ch);
				
			throw new OmiseException($error);
		}
		// 解放
		curl_close($ch);
		// 連想配列に格納し、エラーチェック
		$array = json_decode($result, true);
		if(count($array) === 0) throw new OmiseException('This Exception is unknown.(Bad Response)');
	
		if($array['object'] === 'error') {
			$omiseError = new OmiseError($array);
			throw new OmiseException($omiseError->getMessage().':Please run the "$[this exception]->getOmiseError();" for more information', $omiseError);
		}
	
		return $array;
	}
	
	/**
	 * 引数にリクエストメソッドと、POSTしたい連想配列を渡す。
	 * 戻り値としてphp-curlのオプション配列が帰ってくる。
	 * @param string $requestMethod
	 * @param array $params
	 * @return array
	 */
	private function genOptions($requestMethod, $userpwd, $params) {
		$options = array(
				// HTTPバージョンを1.1に指定
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				// リクエストメソッドの指定
				CURLOPT_CUSTOMREQUEST => $requestMethod,
				// ユーザエージェントの設定
				CURLOPT_USERAGENT => "OmisePHP/".OMISE_PHP_LIB_VERSION." OmiseAPI/".OMISE_API_VERSION,
				// データを文字列で取得する
				CURLOPT_RETURNTRANSFER => true,
				// ヘッダは出力しない
				CURLOPT_HEADER => false,
				// リダイレクトを有効にする
				CURLOPT_FOLLOWLOCATION => true,
				// リダイレクトの最大カウントは3とする
				CURLOPT_MAXREDIRS => 3,
				// リダイレクトが実施されたときヘッダにRefererを追加する
				CURLOPT_AUTOREFERER => true,
				// HTTPレスポンスコード400番台以上はエラーとして扱う
				//CURLOPT_FAILONERROR => true,
				// 実行時間の限界を指定
				CURLOPT_TIMEOUT => OMISE_TIMEOUT,
				// 接続要求のタイムアウトを指定
				CURLOPT_CONNECTTIMEOUT => OMISE_CONNECTTIMEOUT,
				// 認証情報を指定
				CURLOPT_USERPWD => $userpwd
		);
	
		// POSTパラメータがある場合マージ
		if(count($params) > 0) $options += array(CURLOPT_POSTFIELDS => http_build_query($params));
	
		return $options;
	}

	// アクセサ
	protected function setEndPoint($endPoint) {
		$this->_endpoint = $endPoint;
	}
	protected function getEndPoint() {
		return $this->_endPoint;
	}
}