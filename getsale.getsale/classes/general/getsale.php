<?
IncludeModuleLangFile(__FILE__);
use Bitrix\Main\Page\Asset;

Class CGetsaleGetsale {
    function ini() {
        if (defined('ADMIN_SECTION')) return;

        global $APPLICATION;
        $js_code = COption::GetOptionString("getsale.getsale", "getsale_code");
        $js_code = htmlspecialcharsBack($js_code);

        if (!empty($js_code)) {
            $APPLICATION->AddHeadString($js_code, true);

            //добавление/удаление товара
            $js_code = '<script type="text/javascript">
                document.addEventListener("click", function(event){
                    var current = event.srcElement || event.currentTarget || event.target;
                    if (current.id.match("buy_link")) {
                        var productMatches = current.id.match(/([0-9]+)_buy_link/);
                        if (productMatches) {
                            document.cookie = "GETSALE_ADD=Y; path=/;";
                        }
                    }
                });
                document.addEventListener("click", function(event){
                    var current = event.srcElement || event.currentTarget || event.target;
                    if (current.href && (current.href.match("Action=delete") || current.href.match("action=delete"))) {
                        document.cookie = "GETSALE_DEL=Y; path=/;";
                    }
                });

                document.addEventListener("click", function(event){
                    var current = event.srcElement || event.currentTarget || event.target;
                    if (current.href && (current.href.match("Action=delete") || current.href.match("action=delete"))) {
                        document.cookie = "GETSALE_DEL=Y; path=/;";
                    }
                });
                </script>';
            Asset::getInstance()->addString($js_code);

            $js_code = "<script>
                function getCookie(name) {
                  var matches = document.cookie.match(new RegExp(\"(?:^|; )\" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + \"=([^;]*)\"));
                  return matches ? decodeURIComponent(matches[1]) : 'N';
                }

                var getsale_add = getCookie('GETSALE_ADD');
                if(getsale_add && getsale_add == 'Y') {
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event('add-to-cart');
                                console.log('add-to-cart');
                            });
                        })(window, 'getSaleCallbacks');
                        document.cookie = 'GETSALE_ADD=N; path=/;';
                }

                var getsale_del = getCookie('GETSALE_DEL');
                if(getsale_del && getsale_del == 'Y') {
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event('del-from-cart');
                                console.log('del-from-cart');
                            });
                        })(window, 'getSaleCallbacks');
                        document.cookie = 'GETSALE_DEL=N; path=/;';
                }
                </script>";
            Asset::getInstance()->addString($js_code);

            //просмотр каталога
            if (CModule::IncludeModule("catalog")) {
                $dir = $APPLICATION->GetCurDir();
                $dirs = explode('/', $dir);
                if (($dirs[1] == 'e-store' && empty($dirs[4])) || ($dirs[1] == 'catalog' && empty($dirs[3]))) {
                    $js_code = '<script>
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event("cat-view");
                            });
                        })(window, "getSaleCallbacks");
                    </script>';
                    Asset::getInstance()->addString($js_code);
                }
            }

            //просмотр товара
            if (CModule::IncludeModule("catalog")) {
                $dir = $APPLICATION->GetCurDir();
                $dirs = explode('/', $dir);
                if (($dirs[1] == 'e-store' && !empty($dirs[4])) || ($dirs[1] == 'catalog' && !empty($dirs[3]))) {
                    $js_code = '<script>
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event("item-view");
                                console.log("item-view");
                            });
                        })(window, "getSaleCallbacks");
                    </script>';
                    Asset::getInstance()->addString($js_code);
                }
            }

            //успешное оформление заказа
            if ($APPLICATION->get_cookie("GETSALE_REG_SUCCESS") == "Y") {
                $js_code = "<script>
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event('user-reg');
                                console.log('user-reg');
                            });
                        })(window, 'getSaleCallbacks');
                    </script>";
                Asset::getInstance()->addString($js_code);
                $APPLICATION->set_cookie("GETSALE_REG_SUCCESS", "N");
            }

            if (CModule::IncludeModule("catalog")) {
                $dir = $APPLICATION->GetCurDir();
                $dirs = explode('/', $dir);
                if ($dirs[1] == 'personal' && $dirs[2] == 'order' && $dirs[3] == 'make' && empty($dirs[4]) && $_GET['ORDER_ID']) {
                    $js_code = '<script>
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event("success-order");
                                console.log("success-order");
                            });
                        })(window, "getSaleCallbacks");
                    </script>';
                    Asset::getInstance()->addString($js_code);
                }
            }

            if ($APPLICATION->get_cookie("GETSALE_ORDER_SUCCESS") == "Y") {
                $js_code = "<script>
                        (function(w, c) {
                            w[c] = w[c] || [];
                            w[c].push(function(getSale) {
                                getSale.event('success-order');
                                console.log('success-order');
                            });
                        })(window, 'getSaleCallbacks');
                    </script>";
                Asset::getInstance()->addString($js_code);
                $APPLICATION->set_cookie("GETSALE_ORDER_SUCCESS", "N");
            }
        }
    }

    static function order($ID) {
        $arOrder = CSaleOrder::GetByID(intval($ID));
        $filter = Array("EMAIL" => $arOrder['USER_EMAIL']);
        $rsUsers = CUser::GetList(($by = "id"), ($order = "desc"), $filter);
        $res = $rsUsers->Fetch();
        $getsale_id = COption::GetOptionString("getsale.getsale", "getsale_id");

        if (!$getsale_id) return;
        global $APPLICATION;
        $APPLICATION->set_cookie("GETSALE_ORDER_SUCCESS", "Y", time() + 60);

        //регистрация нового пользователя при оформлении заказа
        if ($res['LAST_LOGIN'] == $res['DATE_REGISTER']) {
            $APPLICATION->set_cookie("GETSALE_REG_SUCCESS", "Y", time() + 60);
        }
    }

    //цель регистрация пользователя
    function OnAfterUserRegisterHandler(&$arFields) {
        $getsale_id = COption::GetOptionString("getsale.getsale", "getsale_id");

        if (!$getsale_id) return;

        global $APPLICATION;
        $APPLICATION->set_cookie("GETSALE_REG_SUCCESS", "Y", time() + 60);
    }

    static public function userReg($email, $key) {
        $ch = curl_init();

        $jsondata = json_encode(array('email' => $email, 'key' => $key, 'url' => CGetsaleGetsale::GetCurrUrl(), 'cms' => 'bitrix'));

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_URL, "https://getsale.io/api/registration.json");
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
                      s.src = '//rt.getsale.io/loader.js';
                      if (w.opera == '[object Opera]') {
                          d.addEventListener('DOMContentLoaded', f, false);
                      } else { f(); }
                    })(document, window, 'getSaleInit');
                </script>
                <!-- /GETSALE CODE -->";
        return $jscode;
    }
}