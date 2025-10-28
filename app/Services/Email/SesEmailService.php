<?php 

// namespace App\Services\Email;

// use App\Contracts\EmailServiceInterface;
// use Aws\Ses\SesClient;
// use Aws\Exception\AwsException;

// class SesEmailService implements EmailServiceInterface
// {
//     private $sesClient;

//     public function __construct()
//     {
//         $this->sesClient = new SesClient([
//             'version' => 'latest',
//             'region'  => env('AWS_DEFAULT_REGION'),
//             'credentials' => [
//                 'key'    => env('AWS_ACCESS_KEY_ID'),
//                 'secret' => env('AWS_SECRET_ACCESS_KEY'),
//             ],
//         ]);
//     }

//     public function sendEmail(string $to, string $subject, string $body): bool
//     {
//         try {
//             $result = $this->sesClient->sendEmail([
//                 'Destination' => [
//                     'ToAddresses' => [$to],
//                 ],
//                 'Message' => [
//                     'Body' => [
//                         'Text' => [
//                             'Charset' => 'UTF-8',
//                             'Data' => $body,
//                         ],
//                     ],
//                     'Subject' => [
//                         'Charset' => 'UTF-8',
//                         'Data' => $subject,
//                     ],
//                 ],
//                 'Source' => env('MAIL_FROM_ADDRESS'),
//             ]);

//             return isset($result['MessageId']);
//         } catch (AwsException $e) {
//             // Log the error or handle it as needed
//             return false;
//         }
//     }
// }