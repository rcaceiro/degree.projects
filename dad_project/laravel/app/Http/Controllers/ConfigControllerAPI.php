<?php

namespace App\Http\Controllers;

use App\Config;
use App\Http\Requests\UpdateEmailFormRequest;
use App\Mail\ReasonBlockedMail;
use Illuminate\Mail\TransportManager;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Swift_Mailer;
use Swift_SmtpTransport;

class ConfigControllerAPI extends Controller
{

    public function show($id)
    {
        $config = Config::findOrFail($id);
        $config->props =
            json_decode(str_replace("'", "\"", $config->platform_email_properties));

        return response()->json($config, 200);
    }

    public function update(UpdateEmailFormRequest $request, $id)
    {
        $config = Config::findOrFail($id);
        $config->platform_email = $request["email"];
        $config->platform_email_properties = json_encode($request->platform_email_properties);
        $config->save();

        $host = $request->platform_email_properties["host"];
        $port = $request->platform_email_properties["port"];
        $encryption = $request->platform_email_properties["encryption"];
        $username = $request->platform_email["email"];
        $password = $request->platform_email_properties["password"];

//        config(['mail.driver' => $request->platform_email_properties["driver"]]);
//        config(['mail.host' => $host]);
//        config(['mail.port' => $port]);
//        config(['mail.password' => $password]);
//        config(['mail.encryption' => $encryption]);
//        config(['mail.username' => $username]);
//
//
//        $app = App::getInstance();
//        $app['swift.transport'] = $app->singleton('router',function ($app) {
//            return new TransportManager($app);
//        });
//
//        $mailer = new \Swift_Mailer($app['swift.transport']->driver());
//        Mail::setSwiftMailer($mailer);
//
//        Mail::to('pedroalves-1995@hotmail.com')->send(new ReasonBlockedMail('test'));

        return response()->json('Platform email updated with success!', 200);

    }

}
