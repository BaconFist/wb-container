<?php
/**
 *
 * @category        framework
 * @package         frontend
 * @subpackage      wbmailer
 * @author          Ryan Djurovich, WebsiteBaker Project
 * @copyright       WebsiteBaker Org. e.V.
 * @link            http://websitebaker.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.10.1
 * @requirements    PHP 5.3.6 and higher
 * @version         $Id: class.wbmailer.php 234 2019-03-17 06:05:56Z Luisehahne $
 * @filesource      $HeadURL: svn://isteam.dynxs.de/wb/2.12.x/branches/main/framework/class.wbmailer.php $
 * @lastmodified    $Date: 2019-03-17 07:05:56 +0100 (So, 17. Mrz 2019) $
 * @examples        http://phpmailer.worxware.com/index.php?pg=examples
 *
 */

declare(strict_types=1);

use bin\requester\HttpRequester;
use bin\{WbAdaptor,SecureTokens,Sanitize};

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/* -------------------------------------------------------- */
// Must include code to prevent this file from being accessed directly
if (!\defined('SYSTEM_RUN')) {\header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found'); echo '404 Not Found'; \flush(); exit;}
/* -------------------------------------------------------- */
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
\date_default_timezone_set('Etc/UTC');

// Include PHPMailer autoloader in initialize

class wbmailer extends PHPMailer
{
    // new websitebaker mailer class (subset of PHPMailer class)
    // setting default values

    function __construct($exceptions = false) {//

        parent::__construct($exceptions);//
        $oReg = WbAdaptor::getInstance();
        $oDb  = $oReg->getDatabase();

//        $database = $GLOBALS['database'];
        $errorMessage = [];
        $server_email = ''; // required
        // set mailer defaults (PHP mail function)
        $wbmailer_routine = "phpmail";
        $wbmailer_smtp_host = ""; // required if smtp
        $wbmailer_smtp_port = 25; // required
        $wbmailer_smtp_secure = ''; // required if smtp
        $wbmailer_default_sendername = 'WB Mailer'; // required
// && mb_strlen($wbmailer_smtp_host) > 5
        // get mailer settings from database
        $sql = '
        SELECT * FROM `' .$oDb->TablePrefix. 'settings`
        WHERE `name` LIKE (\'wbmailer\_%\')
           OR `name`=\'server_email\'
        ';
        if ($oRes = $oDb->query($sql)){
            while($aSettings = $oRes->fetchRow( \MYSQLI_ASSOC )) {
                ${$aSettings['name']} = $aSettings['value'];
// TODO sanitize smtp settings
               //if ($wbmailer_routine == "smtp"){
                  switch ($aSettings['name']):
                    case 'server_email':
                          if (\filter_var($aSettings['value'], \FILTER_VALIDATE_EMAIL) === false){
                            $this->setError('Server E-Mail is empty or not valide');
                          };
                          break;
                      case 'wbmailer_smtp_debug':
                          $wbmailer_smtp_debug = (int)$aSettings['value'];
                          break;
                      case 'wbmailer_smtp_host':
                          $wbmailer_smtp_host = $aSettings['value'];
                          break;
                      case 'wbmailer_smtp_port':
                          $wbmailer_smtp_port = (int)$aSettings['value'];
                          break;
                      case 'wbmailer_smtp_secure':
                          $wbmailer_smtp_secure = \strtolower($aSettings['value']);
                          break;
                      case 'wbmailer_low_security':
                          $wbmailer_low_security = \filter_var($aSettings['value'], \FILTER_VALIDATE_BOOLEAN);
                          break;
                      case 'wbmailer_default_sendername':
                      case 'wbmailer_smtp_username':
                      case 'wbmailer_smtp_password':
                          break;
                      default:
                          ${$aSettings['name']} = $aSettings['value'];
                  endswitch;
              //}
            }
        } //end of reading wbmailer_ settings

/**
     * `echo` Output plain-text as-is, appropriate for CLI
     * `html` Output escaped, line breaks converted to `<br>`, appropriate for browser output
     * `error_log` Output to error log as configured in php.ini
     *
     * Alternatively, you can provide a callable expecting two params: a message string and the debug level:
     * <code>
     * $this->Debugoutput = function($str, $level) {echo "debug level $level; message: $str";};
     * </code>
     * * SMTP::DEBUG_OFF (`0`) No debug output, default
     * * SMTP::DEBUG_CLIENT (`1`) Client commands
     * * SMTP::DEBUG_SERVER (`2`) Client commands and server responses
     * * SMTP::DEBUG_CONNECTION (`3`) As DEBUG_SERVER plus connection status
     * * SMTP::DEBUG_LOWLEVEL (`4`) Low-level data output, all messages.
 */

            $this->set('SMTPDebug', $wbmailer_smtp_debug );
            $this->set('Debugoutput', 'html'); // 'error_log' | 'echo'

        // set method to send out emails
        if ($wbmailer_routine == "smtp") {
            // use SMTP for all outgoing mails send by Website Baker
            $this->isSMTP();                                               // telling the class to use SMTP
            $this->set('SMTPAuth', false);                                 // enable SMTP authentication
            $this->set('Host', $wbmailer_smtp_host);                    // Set the hostname of the mail server
            $this->set('Port', \intval($wbmailer_smtp_port));           // Set the SMTP port number - likely to be 25, 465 or 587
            $this->set('SMTPSecure', ($wbmailer_smtp_secure));          // Set the encryption system to use - ssl (deprecated) or tls
            $this->set('SMTPKeepAlive', false);                            // SMTP connection will be close after each email sent
            // check if SMTP authentification is required
            if ($wbmailer_smtp_auth  && (\mb_strlen($wbmailer_smtp_username) > 1) && (\mb_strlen($wbmailer_smtp_password) > 1) ) {
                // use SMTP authentification
                $this->set('SMTPAuth', true);                                                 // enable SMTP authentication
                $this->set('Username',   $wbmailer_smtp_username);                         // set SMTP username
                $this->set('Password',   $wbmailer_smtp_password);                         // set SMTP password
            }
        } else if ($wbmailer_routine == "phpmail") {
            // use PHP mail() function for outgoing mails send by Website Baker
            $this->IsMail();
        } else {
            $this->isSendmail();   // telling the class to use SendMail transport
        }

        // set language file for PHPMailer error messages
        if (\defined("LANGUAGE")) {
            $this->SetLanguage(\strtolower(LANGUAGE),"language");    // english default (also used if file is missing)
        }

        // set default charset
        $this->set('CharSet', 'utf-8');
/*
        if (\defined('DEFAULT_CHARSET')) {
            $this->set('CharSet', DEFAULT_CHARSET);
        } else {
            $this->set('CharSet', 'utf-8');
        }
*/
        // set default sender name
        if ($this->FromName == 'Root User') {
            if (isset($_SESSION['DISPLAY_NAME'])) {
                $this->set('FromName', $_SESSION['DISPLAY_NAME']);                  // FROM NAME: display name of user logged in
            } else {
                $this->set('FromName', $wbmailer_default_sendername);            // FROM NAME: set default name
            }
        }

        /*
            some mail provider (lets say mail.com) reject mails send out by foreign mail
            relays but using the providers domain in the from mail address (e.g. myname@mail.com)
        $this->setFrom($server_email);                       // FROM MAIL: (server mail)
        */

        // set default mail formats
        $this->IsHTML();                                        // Sets message type to HTML or plain.
        $this->set('WordWrap', 80);
        $this->set('Timeout', 30);
    }

    /**
     * Send messages using $Sendmail.
     * @return void
     * @description  overrides isSendmail() in parent
     */
    public function isSendmail()
    {
        $ini_sendmail_path = \ini_get('sendmail_path');
        if (!\preg_match('/sendmail$/i', $ini_sendmail_path)) {
            if ($this->exceptions) {
                throw new phpmailerException('no sendmail available');
            }
        } else {
            $this->Sendmail = $ini_sendmail_path;
            $this->Mailer = 'sendmail';
        }
    }
    public function setLowSecurity()
    {
        $this->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'verify_depth' => 3,
                'allow_self_signed' => true
            ],
            'tls' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'verify_depth' => 3,
                'allow_self_signed' => true
            ]
        ];
    }

} // end of class wbmailer
