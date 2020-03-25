{**
 * plugins/generic/authorDOIScreening/templates/editDOISubmission.tpl
 *
 * Form for editing DOIs during a submission
 *}

<script>
    function noPadrao(doi){ldelim}
        const regex = RegExp("^10[.]\\d{ldelim}4,9{rdelim}\/[-._;()\/:A-Za-z0-9]+$");
        return regex.test(doi);
    {rdelim}

    async function getFromCrossref(doi){ldelim}
        const response = await fetch('https://api.crossref.org/works?filter=doi:' + doi);
        const johnson = response.json();

        return johnson;
    {rdelim}

    async function validaDOI(doiInput, doiError){ldelim}
        if( !noPadrao(doiInput.val()) ){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiValidRequirement"}");
            doiError.css('display', 'block');
            return;
        {rdelim}
        
        const result = await getFromCrossref(doiInput.val());
        const status = result.status, items = result.message.items;

        if(status !=  'ok' || items.length == 0){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiCrossrefRequirement"}");
            doiError.css('display', 'block');
            return;
        {rdelim}

        const authors = items[0]['author'];
        var found = false;
        for(i=0;i<authors.length;i++){ldelim}
            const given1 = authors[i].given, family1 = authors[i].family;
            const given2 = '{$authors[0]->getGivenName('en_US')}',
                family2 = '{$authors[0]->getFamilyName('en_US')}';

            if(given1==given2 && family1==family2){ldelim}
                found = true;
                break;
            {rdelim}
        {rdelim}

        if(!found){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromAuthor"}");
            doiError.css('display', 'block');
            return;
        {rdelim}

        const doiType = items[0]['type'];
        if(doiType != 'journal-article'){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromJournal"}");
            doiError.css('display', 'block');
            return;
        {rdelim}
        
        const anoDOI = items[0]['published-print']['date-parts'][0][0];
        const anoAtual = (new Date()).getFullYear();
        if(anoDOI < anoAtual-2){ldelim}
            doiError.text("{translate key="plugins.generic.authorDOIScreening.doiFromLastThree"}");
            doiError.css('display', 'block');
            return;
        {rdelim}
        
        //Se chegou aqui, tudo esta ok
        if(doiError.css('display') == 'block')
            doiError.css('display', 'none');

    {rdelim}

    function validaCampos(doiInput, doiError){ldelim}
        if($('#firstDOI').val() == $('#secondDOI').val()){ldelim}
            $('#generalError').text("{translate key="plugins.generic.authorDOIScreening.doiDifferentRequirement"}");
            $('#generalError').css('display', 'block');
        {rdelim}
        else {ldelim}
            if($('#generalError').css('display') == 'block')
                $('#generalError').css('display', 'none');

            validaDOI(doiInput, doiError);
        {rdelim}
    {rdelim}

    $(function(){ldelim}
        $('#firstDOI').focusout(function() {ldelim} validaCampos($('#firstDOI'), $('#firstDOIError')) {rdelim});
        $('#secondDOI').focusout(function() {ldelim} validaCampos($('#secondDOI'), $('#secondDOIError')) {rdelim});
    {rdelim});
</script>

<link rel="stylesheet" type="text/css" href="/plugins/generic/authorDOIScreening/styles/submissionEdit.css">

{fbvFormSection}
    <label>
        {translate key="plugins.generic.authorDOIScreening.nome"}
        <span class="req">*</span>
    </label>
    <p class="description">{translate key="plugins.generic.authorDOIScreening.submission.description"}</p>
    <span id="generalError" class="error" style="display:none"></span>
    <div class="pkpFormGroup__fields">
        <div class="pkpFormField">
            <span id="firstDOIError" class="error" style="display:none"></span>
            <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.first"}</label>
            <input id="firstDOI" type="text" name="firstDOI" class="pkpFormField__input required" required="1" validation="required" placeholder="Ex.: 10.1000/182">
        </div>
        <div class="pkpFormField">
            <span id="secondDOIError" class="error" style="display:none"></span>
            <label class="pkpFormFieldLabel">{translate key="plugins.generic.authorDOIScreening.submission.second"}</label>
            <input id="secondDOI" type="text" name="secondDOI" class="pkpFormField__input required" required="1" validation="required" placeholder="Ex.: 10.1000/182">
        </div>
    </div>
{/fbvFormSection}