{**
 * plugins/generic/authorDOIScreening/templates/editDOIs.tpl
 *
 * Form for add/edit DOIs from a submission
 *}

{capture assign=actionUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.authorDOIScreening.controllers.ScieloScreeningHandler" op="addDOIs" escape=false}{/capture}

<script>
    var okay = [false, false, false];
    var years = [0,0,0];

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

    function sucesso(){ldelim}
        $('#generalMessage').text("{translate key="plugins.generic.authorDOIScreening.successfulScreening"}");
        $("#generalMessage").removeClass("myError");
        $("#generalMessage").addClass("mySuccess");
        $('#generalMessage').css('display', 'block');

        screeningChecked = true;
        $("#boxCantScreening").css("display", "none");
    {rdelim}

    async function makeSubmit(e){ldelim}
        if($('#firstDOI').val() == $('#secondDOI').val()
            || $('#firstDOI').val() == $('#thirdDOI').val()
            || $('#secondDOI').val() == $('#thirdDOI').val()
        ){ldelim}
            $('#generalMessage').text("{translate key="plugins.generic.authorDOIScreening.doiDifferentRequirement"}");
            $('#generalMessage').css('display', 'block');
            return;
        {rdelim}

        var countOkay = 0;
        for (var i=0;i<3;i++){ldelim}
            if (okay[i] == true) countOkay += 1;
        {rdelim}

        if(countOkay < 2){ldelim}
            $("#generalMessage").text("{translate key="plugins.generic.authorDOIScreening.attentionRules"}");
            $('#generalMessage').css('display', 'block');
            return;
        {rdelim}
        else if(countOkay == 2){ldelim}
            var countAnos = 0;
            const anoAtual = (new Date()).getFullYear();
            for (var i=0;i<3;i++){ldelim}
                if(years[i] >= anoAtual-2) countAnos += 1;
            {rdelim}

            if(countAnos < 2) {ldelim}
                $("#generalMessage").text("{translate key="plugins.generic.authorDOIScreening.attentionRules"}");
                $('#generalMessage').css('display', 'block');
                return;
            {rdelim}
        {rdelim}

        $.post(
            "{$actionUrl}",
            {ldelim}
                submissionId: {$submissionId},
                firstDOI: (okay[0]) ? ($('#firstDOI').val()) : (""),
                secondDOI: (okay[1]) ? ($('#secondDOI').val()) : (""),
                thirdDOI: (okay[2]) ? ($('#thirdDOI').val()) : ("")
            {rdelim},
            sucesso()
        );
    {rdelim}

    async function getFromCrossref(doi){ldelim}
        const response = await fetch('https://api.crossref.org/works?filter=doi:' + doi);
        const johnson = response.json();

        return johnson;
    {rdelim}

    async function validaDOI(doiInput, doiError, flag){ldelim}
        const result = await getFromCrossref(doiInput.val());
        const status = result.status, items = result.message.items;

        if(status !=  'ok' || items.length == 0){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiCrossrefRequirement"}");
            doiError.css('display', 'block');
            okay[flag] = false;
            return;
        {rdelim}

        const authors = items[0]['author'];
        var found = false;
        for(i=0;i<authors.length;i++){ldelim}
            const nome1 = authors[i].given + authors[i].family;
            const nome2 = '{$authors[0]->getGivenName('en_US')}{$authors[0]->getFamilyName('en_US')}';

            if(similarity(nome1,nome2) > 0.35){ldelim}
                found = true;
                break;
            {rdelim}
        {rdelim}

        if(!found){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromAuthor"}");
            doiError.css('display', 'block');
            okay[flag] = false;
            return;
        {rdelim}
        
        const doiType = items[0]['type'];
        if(doiType != 'journal-article'){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromJournal"}");
            doiError.css('display', 'block');
            okay[flag] = false;
            return;
        {rdelim}
        
        try {ldelim}
            years[flag] = items[0]['published-print']['date-parts'][0][0];
        {rdelim}
        catch (e){
            years[flag] = items[0]['published-online']['date-parts'][0][0];
        }
        
        //Se chegou aqui, tudo esta ok
        if(doiError.css('display') == 'block')
            doiError.css('display', 'none');

        okay[flag] = true;
    {rdelim}

    $(function(){ldelim}
        $('#firstDOI').focusout(function () {ldelim} validaDOI($('#firstDOI'), $('#firstDOIError'), 0) {rdelim});
        $('#secondDOI').focusout(function() {ldelim} validaDOI($('#secondDOI'), $('#secondDOIError'), 1) {rdelim});
        $('#thirdDOI').focusout(function() {ldelim} validaDOI($('#thirdDOI'), $('#thirdDOIError'), 2) {rdelim});
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
            <div id="thirdFormField">
                <span id="thirdDOIError" class="myError" style="display:none"></span>
                <label id="thirdDOILabel" class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.third"}</label>
                {if isset($thirdDOI)}
                    <input id="thirdDOI" type="text" name="thirdDOI" placeholder="Ex.: 10.1000/182" value="{$thirdDOI->getDOICode()}">
                {else}
                    <input id="thirdDOI" type="text" name="thirdDOI" placeholder="Ex.: 10.1000/182">
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