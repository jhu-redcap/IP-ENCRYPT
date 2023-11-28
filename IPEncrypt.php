<?php 

namespace JHU\IPEncrypt;

use \REDCap as REDCap;

/**
 * The IPEncrypt class extends the functionality of REDCap External Modules.
 * It provides mechanisms to encrypt IP addresses of survey respondents
 * and uses action tags to manage this encrypted data within REDCap surveys.
 */

class IPEncrypt extends \ExternalModules\AbstractExternalModule
{

    private $tag = '@IP-ENCRYPT';  //assign the tag name

    /**
     * Retrieves action tags used in a given form.
     * This function utilizes a helper class to identify and return action tags that are specifically tagged with @IP-ENCRYPT.
     *
     * @param string $frmnm The name of the form in which action tags are being sought.
     * @return array An array of action tags found in the specified form.
     */
    function getTags($frmnm)  //Use Andy's helper class to get the action tags in use
    {
        if (!class_exists('\JHU\IPEncrypt\ActionTagHelper')) {
            include_once('classes/ActionTagHelper.php');
        }

        $action_tag_results = ActionTagHelper::getActionTags($this->tag,null,$frmnm);
        //print "<pre>" . print_r($action_tag_results,true) . "</pre>"; // print if needed for debug
        return $action_tag_results;
    }

    /**
     * This function is triggered on every page load before the page is rendered.
     * It specifically targets the Action Tags explanation page in REDCap to append a description for the @IP-ENCRYPT action tag.
     *
     * @param int $project_id The ID of the current REDCap project.
     */
    public function redcap_every_page_before_render($project_id) {
        if (PAGE==='Design/action_tag_explain.php') // Check if the current page is the Action Tags explanation page.
        {
            if (REDCap::versionCompare(REDCAP_VERSION, '13.8.5', '<')) {
            // Access the global language array.
            global $lang;
            // Retrieve the last action tag description from the list of action tags.
            $lastActionTagDesc = end(\Form::getActionTags());
            // Define the description for the @IP-ENCRYPT action tag.
            $actDescription = 'Captures the IP address of the survey respondent as an encrypted string.</br> Because multiple survey submissions from the same IP address will result in the same encrypted string, @IP-ENCRYPT can be an important tool in identifying potential duplicate submissions and possible fraud. This action tag is typically used with @HIDDEN-SURVEY and @READONLY. If necessary (and with appropriate approvals) the encrypted string can be decrypted by a REDCap administrator. </br><b>NOTE:</b> Not all submissions from the same IP address represent fraud. @IP-ENCRYPT is intended to be just one piece of the puzzle. </br><b>NOTE:</b> @IP-ENCRYPT will return an empty string until an Encryption Key is provided in the Control Center EM configuration (requires a REDCap administrator). </br><b>NOTE:</b> If the submitter is using a proxy server or VPN, @IP-ENCRYPT will capture the IP address of that server.';
            // Find the language element that corresponds to the last action tag description.
            $langElement = array_search($lastActionTagDesc, $lang);
                // Append the custom description to the last action tag description.
                $lastActionTagDesc .= "</td></tr>";
                $lastActionTagDesc .= $this->makeTagTR('@IP-ENCRYPT', $actDescription);
            // Update the language element in the global language array.
            $lang[$langElement] = rtrim(rtrim(rtrim(trim($lastActionTagDesc), '</tr>')),'</td>');
            }
        }
    }

    /**
     * Creates an HTML table row (`<tr>`) element with details about an action tag.
     * This function is used to display action tag descriptions in a formatted table row.
     *
     * @param string $tag The action tag (e.g., '@IP-ENCRYPT').
     * @param string $description A description of what the action tag does.
     * @return string An HTML string representing the table row.
     */
    protected function makeTagTR($tag, $description)
    {
        // Access global variables.
        global $isAjax, $lang;
        return \RCView::tr(array(),
            // First table data cell: contains the button for adding the tag, if applicable.
            \RCView::td(array('class'=>'nowrap', 'style'=>'text-align:center;background-color:#f5f5f5;color:#912B2B;padding:7px 15px 7px 12px;font-weight:bold;border:1px solid #ccc;border-bottom:0;border-right:0;'),
                ((!$isAjax || (isset($_POST['hideBtns']) && $_POST['hideBtns'] == '1')) ? '' :
                    \RCView::button(array('class'=>'btn btn-xs btn-rcred', 'style'=>'', 'onclick'=>"$('#field_annotation').val(trim('".js_escape($tag)." '+$('#field_annotation').val())); highlightTableRowOb($(this).parentsUntil('tr').parent(),2500);"), $lang['design_171'])
                )
            ) .
            // Second table data cell: displays the action tag itself.
            \RCView::td(array('class'=>'nowrap', 'style'=>'background-color:#f5f5f5;color:#912B2B;padding:7px;font-weight:bold;border:1px solid #ccc;border-bottom:0;border-left:0;border-right:0;'),
                $tag
            ) .
            // Third table data cell: contains the description of the action tag.
            \RCView::td(array('style'=>'font-size:13px;background-color:#f5f5f5;padding:7px;border:1px solid #ccc;border-bottom:0;border-left:0;'),
                $description
            )
        );
    }

    /**
     * This function is executed on a REDCap survey page. It encrypts the respondent's IP address
     * and processes it for use with specified action tags within the survey.
     *
     * @param int $project_id The ID of the current REDCap project.
     * @param string $record The record identifier for the survey response.
     * @param string $instrument The name of the survey instrument.
     */
    function redcap_survey_page($project_id,$record,$instrument)
    {
        // Retrieve the encryption key from system settings.
        $encryption_key = $this->getSystemSetting('encrypt-key');
        // Proceed only if the encryption key is set.
        if(isset($encryption_key))
        {
            // Get the real IP address of the survey respondent.
            $realIP = $_SERVER['REMOTE_ADDR'];
            // Define the ciphering method.
            $ciphering = "AES-256-CTR";
            // Set options for the openssl_encrypt function.
            $options   = 0;
            // Define the encryption IV (Initialization Vector).
            $encryption_iv = '1234567891011121';
            // Encrypt the IP address using the specified settings.
            $encryption = openssl_encrypt($realIP, $ciphering, $encryption_key, $options, $encryption_iv);
            // Retrieve tags associated with the survey instrument.
            $tag_results = $this->getTags($instrument);
            $TagArray = array();
            // Process each tag and store parameters in an array.
            foreach ($tag_results as $tagname)
            {
                foreach ($tagname as $mdaKey => $mdaData) {
                    $TagArray[$mdaKey] = $mdaData["params"];
                }
            }
            // Include JavaScript for processing the tags with the encrypted IP.
            $this->includeJS($TagArray,$encryption);
        }

    }

    /**
     * Injects JavaScript into the REDCap survey page.
     * The script populates fields tagged with specific action tags with an encrypted IP address.
     *
     * @param array $taggedFields An array containing fields to be tagged with specific actions.
     * @param string $encryption The encrypted IP address string.
     */
    protected function includeJS($taggedFields,$encryption)
    {
        ?>
        <script type="text/javascript">
            $(document).ready(function ()
            {
                // Store the encrypted IP address in a variable.
                var rip = '<?php echo $encryption; ?>';
                // Variable to keep track of the number of fields processed.
                var fieldnum = 0;
                // Parse the taggedFields JSON and iterate over each field.
                var taggedFields = JSON.parse('<?php echo json_encode($taggedFields); ?>');
                Object.keys(taggedFields).forEach(key => {
                    fieldnum++;
                    var fldname = key;
                    // Call the function to populate the field with the encrypted IP.
                    GetAndEncryptIP(fldname,rip,fieldnum);
                });
            });

            /**
             * Populates a specified field with the encrypted IP address.
             *
             * @param string ElementName The name of the form element to be populated.
             * @param string rip The encrypted IP address.
             * @param int fieldnum The ordinal number of the field being processed.
             */
            function GetAndEncryptIP(ElementName,rip,fieldnum)
            {
                // Ensure that the encrypted IP is populated only in the first relevant field.
                if(fieldnum == 1)
                {
                    var field = document.getElementsByName(ElementName);
                    if(field[0].value == ''){
                        field[0].value = rip;
                    }
                }
            }

        </script>
<?php
    }
}