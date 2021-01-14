{**
 * plugins/generic/authorDOIScreening/templates/checkPDFStep2.tpl
 *
 * Template that adds a verification for the number of PDFs sent at the step 2 of submission
 *}

{capture assign=checkNumberPdfsUrl}{url router=$smarty.const.ROUTE_COMPONENT component="plugins.generic.authorDOIScreening.controllers.ScieloScreeningHandler" op="checkNumberPdfs" escape=false}{/capture}

 <script>
    var postResponse;

    $(function(){ldelim}
        $(".pkp_button.submitFormButton").removeAttr("type").attr("type", "button");
        $(".pkp_button.submitFormButton").click(async function(){ldelim}
            await $.post(
                "{$checkNumberPdfsUrl}",
                {ldelim}
                    submissionId: {$submissionId},
                {rdelim},
                function (resultado){ldelim}
                    resultado = JSON.parse(resultado);
                    postResponse = resultado;
                {rdelim}
            );

            if(postResponse['statusNumberPdfs'] == 'error') {ldelim}
                alert("{translate key="plugins.generic.authorDOIScreening.required.numberPDFs"}");
                return;
            {rdelim}

            $('#submitStep2Form').submit();
        {rdelim});
    {rdelim});
 </script>