<?
IncludeModuleLangFile(__FILE__);
use Bitrix\Main\Page\Asset;

Class CUptolikeGetsale {
    function ini() {
        if (defined('ADMIN_SECTION')) return;

        global $APPLICATION;
        $js_code = COption::GetOptionString("uptolike.getsale", "getsale_code");
        $js_code = htmlspecialcharsBack($js_code);

        if (!empty($js_code)) {
            $APPLICATION->AddHeadString($js_code, true);
        }
    }

    static public function userReg($email, $key) {
        $ch = curl_init();

        $jsondata = json_encode(array('email' => $email, 'key' => $key, 'url' => CUptolikeGetsale::GetCurrUrl(), 'cms' => 'bitrix'));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_URL, "http://edge.getsale.io/api/registration.json");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $server_output = curl_exec($ch);

        if (empty($server_output)) {
            $info = curl_error($ch);
        }
        curl_close($ch);
        if (!empty($info)) {
            return $info;
        }
        return json_decode($server_output);
    }

    static public function GetCurrUrl() {
        $result = '';
        $default_port = 80;

        if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on')) {
            $result .= 'https://';
            $default_port = 443;
        } else {
            $result .= 'http://';
        }

        $result .= $_SERVER['SERVER_NAME'];

        if ($_SERVER['SERVER_PORT'] != $default_port) {
            $result .= ':' . $_SERVER['SERVER_PORT'];
        }
        return $result;
    }

    static public function jsCode($id) {
        $jscode = "<!-- GETSALE CODE -->
                <script type='text/javascript'>
                    (function(d, w, c) {
                      w[c] = {
                        projectId:" . $id . "
                      };
                      var n = d.getElementsByTagName('script')[0],
                      s = d.createElement('script'),
                      f = function () { n.parentNode.insertBefore(s, n); };
                      s.type = 'text/javascript';
                      s.async = true;
                      s.src = '//rt.edge.getsale.io/loader.js';
                      if (w.opera == '[object Opera]') {
                          d.addEventListener('DOMContentLoaded', f, false);
                      } else { f(); }
                    })(document, window, 'getSaleInit');
                </script>
                <!-- /GETSALE CODE -->";
        return $jscode;
    }
}