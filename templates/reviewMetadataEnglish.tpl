<div
    v-if="errors.metadataEnglish"
    class="submissionWizard__reviewPanel__item"
>
    <template>
        <notification
            v-for="(error, i) in errors.metadataEnglish"
            :key="i"
            type="warning"
        >
            <icon icon="exclamation-triangle"></icon>
            {{ error }}
        </notification>
    </template>
</div>