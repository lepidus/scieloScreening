{**
 * plugins/generic/authorDOIScreening/templates/editDOIs.tpl
 *
 * Form for add/edit DOIs from a submission
 *}

{if isset($firstDOI) || isset($secondDOI)}
    {capture assign=actionUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.authorDOIScreening.controllers.grid.DOIGridHandler" firstDOIId=$firstDOI->getDOIId() secondDOIId=$secondDOI->getDOIId() op="updateDOIs" escape=false}{/capture}
{else}
    {capture assign=actionUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.authorDOIScreening.controllers.grid.DOIGridHandler" op="addDOIs" escape=false}{/capture}
{/if}

<script>
    var firstOK = false, secondOK = false;

    function editDistance(s1, s2) {ldelim}
        s1 = s1.toLowerCase();
        s2 = s2.toLowerCase();

        var costs = new Array();
        for (var i = 0; i <= s1.length; i++) {ldelim}
            var lastValue = i;
            for (var j = 0; j <= s2.length; j++) {ldelim}
                if (i == 0)
                    costs[j] = j;
                else {ldelim}
                    if (j > 0) {ldelim}
                        var newValue = costs[j - 1];
                        if (s1.charAt(i - 1) != s2.charAt(j - 1))
                            newValue = Math.min(Math.min(newValue, lastValue), costs[j]) + 1;
                        costs[j - 1] = lastValue;
                        lastValue = newValue;
                    {rdelim}
                {rdelim}
            {rdelim}
            if (i > 0)
            costs[s2.length] = lastValue;
        {rdelim}
        return costs[s2.length];
    {rdelim}

    function similarity(s1, s2) {ldelim}
        var longer = s1;
        var shorter = s2;
        if (s1.length < s2.length) {ldelim}
            longer = s2;
            shorter = s1;
        {rdelim}
        var longerLength = longer.length;
        if (longerLength == 0) {ldelim}
            return 1.0;
        {rdelim}
        return (longerLength - editDistance(longer, shorter)) / parseFloat(longerLength);
    {rdelim}

    async function makeSubmit(e){ldelim}
        if(firstOK && secondOK){ldelim}
            $.post(
                "{$actionUrl}",
                {ldelim}
                    submissionId: {$submissionId},
                    firstDOI: $('#firstDOI').val(),
                    secondDOI: $('#secondDOI').val()
                {rdelim},
                function(data, status){ldelim}
                    $('#generalMessage').text("{translate key="plugins.generic.authorDOIScreening.successfulScreening"}");
                    $("#generalMessage").removeClass("myError");
                    $("#generalMessage").addClass("mySuccess");
                    $('#generalMessage').css('display', 'block');

                    try {ldelim}
                        screeningChecked = true;
                        $("#boxCantScreening").css("display", "none");
                    {rdelim}
                    catch(e){ldelim}
                    {rdelim}
                {rdelim}
            );
            return true;
        {rdelim}
        return false;
    {rdelim}

    function noPadrao(doi){ldelim}
        const regex = RegExp("^10[.]\\d{ldelim}4,9{rdelim}\/[-._;()\/:A-Za-z0-9]+$");
        return regex.test(doi);
    {rdelim}

    async function getFromCrossref(doi){ldelim}
        const response = await fetch('https://api.crossref.org/works?filter=doi:' + doi);
        const johnson = response.json();

        return johnson;
    {rdelim}

    async function validaDOI(doiInput, doiError, flag){ldelim}
        if( !noPadrao(doiInput.val()) ){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiValidRequirement"}");
            doiError.css('display', 'block');
            (flag == 'first')?(firstOK = false):(secondOK = false);
            return;
        {rdelim}
        
        const result = await getFromCrossref(doiInput.val());
        const status = result.status, items = result.message.items;

        if(status !=  'ok' || items.length == 0){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiCrossrefRequirement"}");
            doiError.css('display', 'block');
            (flag == 'first')?(firstOK = false):(secondOK = false);
            return;
        {rdelim}

        const authors = items[0]['author'];
        var found = false;
        for(i=0;i<authors.length;i++){ldelim}
            const nome1 = authors[i].given + authors[i].family;
            const nome2 = '{$authors[0]->getGivenName('en_US')}{$authors[0]->getFamilyName('en_US')}';

            if(similarity(nome1,nome2) > 0.8){ldelim}
                found = true;
                break;
            {rdelim}
        {rdelim}

        if(!found){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromAuthor"}");
            doiError.css('display', 'block');
            (flag == 'first')?(firstOK = false):(secondOK = false);
            return;
        {rdelim}
        
        const doiType = items[0]['type'];
        if(doiType != 'journal-article'){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromJournal"}");
            doiError.css('display', 'block');
            (flag == 'first')?(firstOK = false):(secondOK = false);
            return;
        {rdelim}
        
        const anoDOI = items[0]['published-print']['date-parts'][0][0];
        const anoAtual = (new Date()).getFullYear();
        if(anoDOI < anoAtual-2){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromLastThree"}");
            doiError.css('display', 'block');
            (flag == 'first')?(firstOK = false):(secondOK = false);
            return;
        {rdelim}
        
        //Se chegou aqui, tudo esta ok
        if(doiError.css('display') == 'block')
            doiError.css('display', 'none');


        (flag == 'first')?(firstOK = true):(secondOK = true);
    {rdelim}

    function validaCampos(doiInput, doiError, doiFlag){ldelim}
        if($('#firstDOI').val() == $('#secondDOI').val()){ldelim}
            $('#generalMessage').text("{translate key="plugins.generic.authorDOIScreening.doiDifferentRequirement"}");
            $('#generalMessage').css('display', 'block');
        {rdelim}
        else {ldelim}
            if($('#generalMessage').css('display') == 'block')
                $('#generalMessage').css('display', 'none');

            validaDOI(doiInput, doiError, doiFlag);
        {rdelim}
    {rdelim}

    $(function(){ldelim}
        $('#firstDOI').focusout(function () {ldelim} validaCampos($('#firstDOI'), $('#firstDOIError'), 'first') {rdelim});
        $('#secondDOI').focusout(function() {ldelim} validaCampos($('#secondDOI'), $('#secondDOIError'), 'second') {rdelim});
        $('#doiSubmit').click(makeSubmit);
    {rdelim});
</script>

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/submissionForm.css">

<div id="doiForm">
    <div id="doiFormArea">
        <h2>{translate key="plugins.generic.authorDOIScreening.nome"}</h2>
        <p>{translate key="plugins.generic.authorDOIScreening.submission.description"}</p>
        <span id="generalMessage" class="myError" style="display:none"></span>
        <div id="formFields">
            <div id="firstFormField">
                <span id="firstDOIError" class="myError" style="display:none"></span>
                <label id="firstDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.first"}</label>
                {if isset($firstDOI)}
                    <input id="firstDOI" type="text" name="firstDOI" placeholder="Ex.: 10.1000/182" value="{$firstDOI->getDOICode()}">
                {else}
                    <input id="firstDOI" type="text" name="firstDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
            <div id="secondFormField">
                <span id="secondDOIError" class="myError" style="display:none"></span>
                <label id="secondDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.second"}</label>
                {if isset($secondDOI)}
                    <input id="secondDOI" type="text" name="secondDOI" placeholder="Ex.: 10.1000/182" value="{$secondDOI->getDOICode()}">
                {else}
                    <input id="secondDOI" type="text" name="secondDOI" placeholder="Ex.: 10.1000/182">
                {/if}
            </div>
        </div>
    </div>
    <div id="doiFormFooter">
        <div id="formButtons">
            <button id="doiSubmit" type="button" class="pkpButton">{translate key="common.save"}</button>
        </div>
    </div>
</div>