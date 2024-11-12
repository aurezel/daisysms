<?php 
/*
 module:		消息管理
 create_time:	2020-07-25 20:01:31
 author:		
 contact:		
*/

namespace app\dashboard\service;
//use app\admin\model\Message;
use think\exception\ValidateException;
use xhadmin\CommonService;

class WalletService extends CommonService {


	/*
 	* @Description  列表数据
 	*/
	public static function indexList($where,$field,$order,$limit,$page){
		try{
			$res = db('message')->field($field)->alias('a')->join('user b','a.customerid=b.user_id','left')->where($where)->order($order)->paginate(['list_rows'=>$limit,'page'=>$page]);
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		return ['rows'=>$res->items(),'total'=>$res->total()];
	}


	/*
 	* @Description  添加
 	*/
	public static function add($data){
		try{
			$data['timestamp'] = strtotime($data['timestamp']);
			$res = Message::create($data);
		}catch(ValidateException $e){
			throw new ValidateException ($e->getError());
		}catch(\Exception $e){
			abort(config('my.error_log_code'),$e->getMessage());
		}
		if(!$res){
			throw new ValidateException ('操作失败');
		}
		return $res->mid;
	}


	/*
 	* @Description  修改
 	*/

    function sendPaymentRequest($data) {

        $url = 'https://center.sdapay.shop/zzpay/getPayUrl';

        $token = 'TZlWaiIAXdcJkiA5M1yeen6PRbnRuzvhKlm31wEXf5U=';



        // Use the actual user agent from the current request

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $host = $_SERVER['HTTP_HOST'] ?? '';


        // Prepare the JSON payload as an array

        $_data = [

            "host" => $host,

            "username" => "EB7B660C-A881-2194-2CE4-F95E3DABAD8F",

            "email" => "test123@test.com",

            "user_agent" => $user_agent,

            "client_ip" => $_SERVER['REMOTE_ADDR'], //"192.168.5.12",

            "invoice_id" => $data['id'],

            "order_no" => $data['id'],

            "amount" => $data['amount'],

            "currency" => "USD",

            "country" => "US",

            "state" => "Florida",

            "telephone" => "3105779329",

            "success_uri" => "https://{$host}/dashboard/history/payments",

            "notify_url" => "https://{$host}/dashboard/wallet",

            "order_product" => [

                [

                    "name" => "shoes",

                    "order_product_id" => 666556,

                    "quantity" => 1,

                    "price" => 10.55,

                    "goods_image" => "https://howsms.com/api/gateway/sms",

                    "attributes" => [

                        ["name" => "NIKE", "value" => "Yellow"]

                    ],

                    "product_id" => 1000,

                    "order_id" => "1111",

                    "total" => 66.66,

                    "tax" => 0.00,

                    "model" => "Kongjun",

                    "reward" => 0.00,

                    "product_url" => "https://howsms.com/api/gateway/sms"

                ]

            ],

            "order_info" => [

                "payment_firstname" => "Damid",

                "payment_lastname" => "Yang",

                "user_agent" => $user_agent,

                "payment_address_1" => "2681 Juniper Drive",

                "payment_address_2" => "Midland, MI 48640",

                "payment_city" => "Venice",

                "payment_postcode" => "90291",

                "remark" => "test"

            ],

            "bn" => "woocommerce"

        ];



        $ch = curl_init();



        // Set up cURL options

        curl_setopt_array($ch, [

            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [

                'token: ' . $token,

                'Content-Type: application/json'

            ],

            CURLOPT_POSTFIELDS => json_encode($_data), // Convert the array to JSON

            CURLOPT_SSL_VERIFYPEER => false,

            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_TIMEOUT => 60

        ]);



        // Execute the request

        $response = curl_exec($ch);



        // Handle errors

        if (curl_errno($ch)) {

            $error_msg = curl_error($ch);

            curl_close($ch);

            throw new Exception("cURL error: $error_msg");

        }



        curl_close($ch);



        // Return the decoded response

        return json_decode($response, true);

    }

    function sendHttpsRequest($data) {

        $url = 'https://center.wwdpay.com/zzpay/getPayUrl';
        $token = 'TZlWaiIAXdcJkiA5M1yeen6PRbnRuzvhKlm31wEXf5U=';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $_data = [
            "host" => $host,
            "username" => "E9848628-CCBA-9FC5-6759-15BF0BDEA5AA",

            "email" => "auglethee@gmail.com",

            "user_agent" => $user_agent,

            "client_ip" => $_SERVER['REMOTE_ADDR'], //"192.168.5.12",

            "invoice_id" => $data['id'],

            "order_no" => $data['id'],

            "amount" => $data['amount'],

            "currency" => "USD",

            "country" => "US",

            "state" => "Florida",

            "telephone" => "3105779329",

            "success_uri" => "https://{$host}/dashboard/history/payments",

            "notify_url" => "https://{$host}/dashboard/wallet",

            "order_product" => [

                [

                    "name" => "shoes",

                    "order_product_id" => 666556,

                    "quantity" => 1,

                    "price" => 10.55,

                    "goods_image" => "https://howsms.com/api/gateway/sms",

                    "attributes" => [

                        ["name" => "NIKE", "value" => "Yellow"]

                    ],

                    "product_id" => 1000,

                    "order_id" => "1111",

                    "total" => 66.66,

                    "tax" => 0.00,

                    "model" => "Kongjun",

                    "reward" => 0.00,

                    "product_url" => "https://howsms.com/api/gateway/sms"

                ]

            ],

            "order_info" => [

                "payment_firstname" => "Damid",

                "payment_lastname" => "Yang",

                "user_agent" => $user_agent,

                "payment_address_1" => "2681 Juniper Drive",

                "payment_address_2" => "Midland, MI 48640",

                "payment_city" => "Venice",

                "payment_postcode" => "90291",

                "remark" => "test"

            ],

            "bn" => "woocommerce"

        ];



        $ch = curl_init();



        // Set up cURL options

        curl_setopt_array($ch, [

            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_HTTPHEADER => [

                'token: ' . $token,

                'Content-Type: application/json'

            ],

            CURLOPT_POSTFIELDS => json_encode($_data), // Convert the array to JSON

            CURLOPT_SSL_VERIFYPEER => false,

            CURLOPT_SSL_VERIFYHOST => false,

            CURLOPT_TIMEOUT => 60

        ]);



        // Execute the request

        $response = curl_exec($ch);



        // Handle errors

        if (curl_errno($ch)) {

            $error_msg = curl_error($ch);

            curl_close($ch);

            throw new Exception("cURL error: $error_msg");

        }



        curl_close($ch);



        // Return the decoded response

        return json_decode($response, true);

    }
     

}

