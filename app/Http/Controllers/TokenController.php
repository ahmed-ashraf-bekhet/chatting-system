<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;
use Twilio\Rest\Client;
use App\Join;
use Illuminate\Support\Facades\DB;

use function PHPSTORM_META\type;

class TokenController extends Controller
{

    public function generate(Request $request)
    {
        $user = $request->input("identity");
        $token = new AccessToken('AC00c454dc839c77f91ab5b7c77ab8b7f4','SK89a378b3240ea8805b111d22ef1124d2'
        ,'mMH4gKIXVNvsMjZquy8W1HLkIAA7k23H',3600,$user);

        $chatGrant = new ChatGrant();
        $chatGrant->setServiceSid('ISc4e202cd257a4b7da27ee307474c4fd7');
        $token->addGrant($chatGrant);

        $response = array(
            'identity' => $user,
            'token' => $token->toJWT(),
        );
        return response()->json($response);
    }

    public function joiningCheck(Request $request)
    {
        $email = $request->email;
        $res = Join::where('email',$email)->get();
        if($res->isEmpty()){
            $join = new Join;
            $join->email = $email;
            $join->save();
            return response()->json(true);
        }
        return response()->json(false);
    }

    public function createChannel(Request $request)
    {
        $sid    = "AC00c454dc839c77f91ab5b7c77ab8b7f4";
        $token  = "8f775641886be9343e928ee05e42563d";
        $twilio = new Client($sid, $token);
        $uniqueName = $request->email1."-".$request->email2;
        $result = DB::table('channels')->select('sid')->where('email1',$request->email1)->where('email2',$request->email2)->get();
        $result2 = DB::table('channels')->select('sid')->where('email1',$request->email2)->where('email2',$request->email1)->get();
        if(!$result->isEmpty() || !$result2->isEmpty()){
            if(!$result->isEmpty()){
                return $result[0]->sid;
            }
            else{
                return $result2[0]->sid;
            }
        }
        $channel =  $twilio->chat->v2->services("ISc4e202cd257a4b7da27ee307474c4fd7")
                    ->channels
                    ->create([
                        'uniqueName' => $uniqueName
                    ]);
        $member1  =  $twilio->chat->v2->services("ISc4e202cd257a4b7da27ee307474c4fd7")
                    ->channels($uniqueName)
                    ->members
                    ->create($request->email1);
        $member2  =  $twilio->chat->v2->services("ISc4e202cd257a4b7da27ee307474c4fd7")
                    ->channels($uniqueName)
                    ->members
                    ->create($request->email2);
        DB::table('channels')->insert(
            ['sid'=>$channel->sid,'email1'=>$request->email1,'email2'=>$request->email2]
        );
        return $channel->sid;
    }
}
