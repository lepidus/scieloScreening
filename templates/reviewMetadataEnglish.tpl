<div
    v-if="errors.metadataEnglish"
    class="submissionWizard__reviewPanel__item"
>
    <notification
        v-for="(error, i) in errors.metadataEnglish"
        :key="i"
        type="warning"
    >
        <icon icon="Error" class="h-5 w-5" :inline="true"></icon>
        {{ error }}
    </notification>
</div>