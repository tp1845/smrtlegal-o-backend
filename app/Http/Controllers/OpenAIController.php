<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OpenAIController extends Controller
{
    public function create_req(Request $request) {
        $post = json_decode(file_get_contents('php://input'), true);

        $prompt = ! empty($post['prompt']) ? $post['prompt'] : 'Summarize this for a second-grade student: ';
        $max_tokens = ! empty($post['max_tokens']) ? (integer)$post['max_tokens'] : 1000;
        $content = ! empty($post['content']) ? $post['content'] : '';

        $content = ltrim(strip_tags($content), '"');

        // $content = substr($content, 0, 400);

        $data = json_encode([
            "model" => "text-davinci-003",
            "prompt" => "' . $prompt . $content . '",
            "temperature" => 0.7,
            "max_tokens" => $max_tokens,
            "top_p" => 1,
            "frequency_penalty" => 0,
            "presence_penalty" => 0
        ]);

        $encrypted_key = "CrpZPV/ryS3HdtQvtRsp/WZhdmR4VGN6enRwVGlUTHkvVElacXBxTmJqVHRCa2QydHc0MTVCaDhmOExZQ1pNckxHVUduQUorblkzbjZGcm1VWG9pQzNURUxkL3Zva3BTOUppeFpBPT0=";
        $decrypted_key = $this->mydecrypt("encryption_key", $encrypted_key);

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.openai.com/v1/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer '.$decrypted_key
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        return response()->json([
            'data' => $response,
        ]);
    }
    
    public function has_content(Request $request)
    {
        $inputValue = $request->input('content');
        $result = false;
        $response = 'Test Message';
        return response()->json([
            'result' => $result,
            'data' => $response
        ]);
    }

    public function get_response(Request $request)
    {
        $content = $request->input('content');
    //    $member = TeamMember::where('team_id', $team->id)->where('user_id', $user->id)->first();
        
        $response = DB::table('ai_response')
                    ->select('content')
                    ->where()
                    ->get();
        $result = true;
        $response = 'Test Message';
        return response()->json([
            'result' => $result,
            'data' => $response
        ]);
    }

    public function myencrypt($key, $data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    public function mydecrypt($key, $encryptedData) {
        $encryptedData = base64_decode($encryptedData);
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($encryptedData, 0, $ivLength);
        $encryptedPayload = substr($encryptedData, $ivLength);
        return openssl_decrypt($encryptedPayload, 'aes-256-cbc', $key, 0, $iv);
    }
}
