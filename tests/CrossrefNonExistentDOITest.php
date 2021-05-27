<?php

use PHPUnit\Framework\TestCase;
require "DOISystemClientForTests.inc.php";

final class CrossrefNonExistentDOITest extends TestCase
{
    private $crossrefNonExistentDOI;

    function setUp() : void {
        $this->crossrefNonExistentDOI = new CrossrefNonExistentDOI("10.1145/1998076.1998132");
    }

    public function testIsInvalidWhenResultsOnAHTTP302FromDOIOrg(): void
    {
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(302));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => "Apenas DOIs da Crossref são aceitos"
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }


    public function testIsInvalidWhenResultsOnAHTTP500FromDOIOrg(): void
    {
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(500));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => "Erro no servidor DOI.org"
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP404FromDOIOrg(): void
    {
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(404));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => "O DOI inserido não está registrado. Confirme se o mesmo está correto e, em caso de dúvida, verifique com a publicação de origem."
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenResultsOnAHTTP408FromDOIOrg(): void
    {
        $httpStatus = 408;
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests($httpStatus));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => "Código de retorno" . $httpStatus . "desconhecido de DOI.org. Tente novamente em alguns instantes e, caso o problema persista, por favor avise"
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

    public function testIsInvalidWhenFailureWithCommunicationsWithDOIOrg(): void
    {
        $exceptionWithCommunication = true;
        $this->crossrefNonExistentDOI->setClient(new DOISystemClientForTests(null, $exceptionWithCommunication));

        $expectedValidationResult =  [
            'statusValidate' => CrossrefNonExistentDOI::VALIDATION_ERROR_STATUS,
            'messageError' => "Falha na comunicação com DOI.org. Tente novamente em alguns instantes e, caso o problema persista, por favor avise"
        ];
        
        $validationResult = $this->crossrefNonExistentDOI->getErrorMessage();
        $this->assertEquals($expectedValidationResult, $validationResult);
    }

}