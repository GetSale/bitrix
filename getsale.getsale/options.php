<?php

use Bitrix\Main\Localization\Loc;

if (!$USER->IsAdmin()) {
    return;
}

define('ADMIN_MODULE_NAME', 'getsale.getsale');


if ($APPLICATION->GetGroupRight(ADMIN_MODULE_NAME) >= 'R') {

    Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
    Loc::loadMessages(__FILE__);

    $tabControl = new CAdminTabControl("tabControl", array(array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),));

    if ((!empty($save) || !empty($restore)) && $REQUEST_METHOD == "POST" && check_bitrix_sessid()) {
        if (!empty($restore)) {
            COption::RemoveOption(ADMIN_MODULE_NAME);
            CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("OPTIONS_RESTORED"), "TYPE" => "OK"));
        } else {
            $is_saved = false;

            $getsale_id = 'getsale_id';
            $getsale_code = "getsale_code";
            $getsale_mail = "getsale_mail";
            $getsale_key = "getsale_key";

            if (!empty($_REQUEST[$getsale_mail])) {
                COption::SetOptionString(ADMIN_MODULE_NAME, $getsale_mail, $_REQUEST[$getsale_mail], Loc::getMessage("GETSALE_MAIL"));
                $is_saved = true;
            } else {
                CAdminMessage::ShowMessage(Loc::getMessage("ERROR_MAIL_EMPTY"));
            }
            if (!empty($_REQUEST[$getsale_key])) {
                COption::SetOptionString(ADMIN_MODULE_NAME, $getsale_key, $_REQUEST[$getsale_key], Loc::getMessage("GETSALE_KEY"));
                $is_saved = true;
            }

            if ($is_saved) {
                $json_result = CGetsaleGetsale::userReg($_REQUEST['getsale_mail'], $_REQUEST['getsale_key']);
                if (isset($json_result->status)) {
                    if (($json_result->status == 'OK')) {
                        $val_getsale_id = $json_result->payload->projectId;
                        COption::SetOptionString(ADMIN_MODULE_NAME, 'getsale_id', $json_result->payload->projectId, '');
                        $val_getsale_code = CGetsaleGetsale::jsCode($val_getsale_id);
                        COption::SetOptionString(ADMIN_MODULE_NAME, 'getsale_code', htmlspecialchars($val_getsale_code), '');
                        CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage('GETSALE_ID_SUCCESS'), "TYPE" => "OK"));
                    } elseif ($json_result->status == 'error') {
                        if ($json_result->code == '403') {
                            $json_result->message = Loc::getMessage('GETSALE_TAB_MESS_3');
                        }
                        if ($json_result->code == '500') {
                            $json_result->message = Loc::getMessage('GETSALE_TAB_MESS_4');
                        }
                        if ($json_result->code == '404') {
                            $json_result->message = Loc::getMessage('GETSALE_TAB_MESS_5');
                        }
                        if (!isset($json_result->code)) {
                            $json_result->message = Loc::getMessage('GETSALE_TAB_MESS_6');
                        }

                        CAdminMessage::ShowMessage(array("MESSAGE" => $json_result->message, "TYPE" => "ERROR"));
                    }
                } else {
                    CAdminMessage::ShowMessage(array("MESSAGE" => Loc::getMessage("GETSALE_TAB_MESS_7") . ' ' . $json_result, "TYPE" => "ERROR"));
                }
            }
        }
    }

    $tabControl->Begin();
    $val_getsale_id = COption::GetOptionString(ADMIN_MODULE_NAME, 'getsale_id');
    $val_getsale_code = COption::GetOptionString(ADMIN_MODULE_NAME, 'getsale_code');
    ?>

    <form method="post"
          action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($mid) ?>&amp;lang=<?= LANGUAGE_ID ?>">

        <?php if (!function_exists('curl_exec')): ?>
            <div class="adm-info-message-wrap">
                <div class="adm-info-message">
                    <span
                        class="required"><?= Loc::getMessage("CURL_DISABLED_MESSAGE") ?></span><br/>
                    <?= Loc::getMessage("HOSTING_SUPPORT") ?>
                </div>
            </div>
        <?php endif; ?>

        <? $tabControl->BeginNextTab(); ?>

        <?php
        $getsale_mail = 'getsale_mail';
        $getsale_key = 'getsale_key';
        $getsale_id = 'getsale_id';
        $getsale_code = 'getsale_code';

        $val_getsale_mail = COption::GetOptionString(ADMIN_MODULE_NAME, 'getsale_mail');
        $val_getsale_key = COption::GetOptionString(ADMIN_MODULE_NAME, 'getsale_key');
        ?>

        <tr class="heading">
            <td colspan="2"><b><?= GetMessage('GETSALE_TAB_HEADER') ?></b></td>
        </tr>


        <tr>
            <td width="40%">
                <label for="<?= $getsale_mail ?>"><?= Loc::getMessage("GETSALE_MAIL") ?>:</label>
            <td width="60%">
                <input type="text" size="50" name="<?= $getsale_mail ?>"
                       value="<?= $val_getsale_mail; ?>" <? echo (!empty($val_getsale_id)) ? 'disabled' : ''; ?>>
                <? if (!empty($val_getsale_id)): ?>
                    <div
                        style="background-image: url('../images/<?= ADMIN_MODULE_NAME ?>/ok.png');width: 16px;height: 16px;margin: -4px -22px;display: inline-block;"></div>
                <? endif; ?>
            </td>
        </tr>

        <tr>
            <td width="40%">
                <label for="<?= $getsale_key ?>"><?= Loc::getMessage("GETSALE_KEY") ?>:</label>
            <td width="60%">
                <input type="text" size="50" name="<?= $getsale_key ?>"
                       value="<?= $val_getsale_key; ?>" <? echo (!empty($val_getsale_id)) ? 'disabled' : ''; ?>>
                <? if (!empty($val_getsale_id)): ?>
                    <div
                        style="background-image: url('../images/<?= ADMIN_MODULE_NAME ?>/ok.png');width: 16px;height: 16px;margin: -4px -22px;display: inline-block;"></div>
                <? endif; ?>
            </td>
        </tr>

        <tr>
            <input type="hidden" name="<?= $getsale_id ?>"
                   value="<?= $val_getsale_id; ?>">
            <input type="hidden" name="<?= $getsale_code ?>"
                   value="<?= htmlspecialcharsbx($val_getsale_code) ?>">
        </tr>

        <tr>
            <td colspan="2">
                <? if (!empty($val_getsale_id)): ?>
                    <?= GetMessage("GETSALE_TAB_TEXT3") ?> <a
                        href="https://getsale.io" target="_blank"><?= GetMessage("GETSALE_TITLE") ?></a><br><br>
                <? else: ?>
                    <?= GetMessage("GETSALE_TAB_TEXT1") ?> <a
                        href="https://getsale.io" target="_blank"><?= GetMessage("GETSALE_TITLE") ?></a><br><br>
                    <?= GetMessage("GETSALE_TAB_TEXT2") ?> <a
                        href="https://getsale.io" target="_blank"><?= GetMessage("GETSALE_TITLE") ?></a><br><br>
                <? endif; ?>
                <?= GetMessage("GETSALE_TAB_TEXT4") ?> <a
                    href='mailto:support@getsale.io'>support@getsale.io</a><br><br>
                <?= GetMessage("GETSALE_TAB_TEXT5") ?><br><br>
            </td>
        </tr>

        <? $tabControl->Buttons(); ?>
        <? if (empty($val_getsale_id)): ?>
            <input type="submit" name="save" value="<?= GetMessage("MAIN_SAVE") ?>"
                   title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>" class="adm-btn-save">
            <input type="submit" name="restore" title="<?= GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
                   OnClick="return confirm('<?= AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
                   value="<?= GetMessage("MAIN_RESTORE_DEFAULTS") ?>">
            <?= bitrix_sessid_post(); ?>
        <? endif; ?>
        <? $tabControl->End(); ?>

    </form>
    <style>
        .adm-detail-content-table > tbody > .heading td {
            text-align: left !important;
        }

        .adm-detail-content-table > tbody > .heading td > b {
            font-weight: normal !important;
        }
    </style>
    <?php
}
?>
