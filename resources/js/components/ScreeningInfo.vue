<template>
  <div class="screeningInfoArea">
    <div class="screeningInfoHeader">
      <h2>{{ t("plugins.generic.scieloScreening.info.name") }}</h2>
      <p>{{ t("plugins.generic.scieloScreening.info.description") }}</p>
    </div>

    <div v-if="isLoading" class="screeningInfoLoading">
      <PkpSpinner />
    </div>

    <div v-else-if="error" class="screeningInfoError">
      <p>{{ error }}</p>
    </div>

    <div v-else class="screeningInfoFields">
      <!-- Metadata English -->
      <div class="screeningInfoField">
        <div class="screeningInfoFieldHeader">
          <span
            :class="screeningData.statusMetadataEnglish
              ? 'screeningStatusOkay'
              : 'screeningStatusNotOkay'"
          ></span>
          <span v-if="screeningData.statusMetadataEnglish">
            {{ t("plugins.generic.scieloScreening.info.metadataEnglishOkay") }}
          </span>
          <span v-else>
            {{
              t("plugins.generic.scieloScreening.info.metadataEnglishNotOkay", {
                missingMetadata: screeningData.missingMetadataEnglish,
              })
            }}
          </span>
        </div>
      </div>

      <!-- Affiliation -->
      <div class="screeningInfoField">
        <div class="screeningInfoFieldHeader">
          <span
            :class="screeningData.statusAffiliation
              ? 'screeningStatusOkay'
              : 'screeningStatusNotOkay'"
          ></span>
          <span v-if="screeningData.statusAffiliation">
            {{ t("plugins.generic.scieloScreening.info.affiliationOkay") }}
          </span>
          <span v-else>
            {{ t("plugins.generic.scieloScreening.info.affiliationNotOkay") }}
          </span>
        </div>
        <div
          v-if="!screeningData.statusAffiliation
            && screeningData.authorsWithoutAffiliation?.length"
          class="screeningInfoFieldBody"
        >
          <ul>
            <li
              v-for="author in screeningData.authorsWithoutAffiliation"
              :key="author"
            >
              {{ author }}
            </li>
          </ul>
        </div>
      </div>

      <!-- ORCID -->
      <div class="screeningInfoField">
        <div class="screeningInfoFieldHeader">
          <span
            :class="screeningData.statusOrcid
              ? 'screeningStatusOkay'
              : 'screeningStatusNotOkay'"
          ></span>
          <span v-if="screeningData.statusOrcid">
            {{ t("plugins.generic.scieloScreening.info.orcidOkay") }}
          </span>
          <span v-else>
            {{ t("plugins.generic.scieloScreening.info.orcidNotOkay") }}
          </span>
        </div>
      </div>

      <!-- PDFs -->
      <div class="screeningInfoField">
        <div class="screeningInfoFieldHeader">
          <span
            :class="screeningData.statusPDFs
              ? 'screeningStatusOkay'
              : 'screeningStatusNotOkay'"
          ></span>
          <span v-if="screeningData.numPDFs === 0">
            {{ t("plugins.generic.scieloScreening.info.noPDFs") }}
          </span>
          <span v-else-if="screeningData.numPDFs > 1">
            {{ t("plugins.generic.scieloScreening.info.manyPDFs.header") }}
          </span>
          <span v-else>
            {{ t("plugins.generic.scieloScreening.info.pdfsOkay") }}
          </span>
        </div>
        <div v-if="screeningData.numPDFs > 1" class="screeningInfoFieldBody">
          <ul
            v-html="t('plugins.generic.scieloScreening.info.manyPDFs.body')"
          ></ul>
        </div>
      </div>

      <!-- Document ORCIDs -->
      <div class="screeningInfoField">
        <div class="screeningInfoFieldHeader">
          <span
            :class="getDocumentOrcidsStatusClass(
              screeningData.statusDocumentOrcids
            )"
          ></span>
          <span v-if="screeningData.statusDocumentOrcids === 'Okay'">
            {{ t("plugins.generic.scieloScreening.info.documentOrcidsOkay") }}
          </span>
          <span v-else-if="screeningData.statusDocumentOrcids === 'UnableNoFile'">
            {{ t("plugins.generic.scieloScreening.info.documentOrcidsUnableNoFile") }}
          </span>
          <span v-else-if="screeningData.statusDocumentOrcids === 'UnableNoOrcids'">
            {{ t("plugins.generic.scieloScreening.info.documentOrcidsUnableNoOrcids") }}
          </span>
          <span v-else-if="screeningData.statusDocumentOrcids === 'UnableException'">
            {{ t("plugins.generic.scieloScreening.info.documentOrcidsUnableException") }}
          </span>
          <span v-else>
            {{
              t(
                "plugins.generic.scieloScreening.info.documentOrcidsNotOkay.header"
              )
            }}
          </span>
        </div>
        <div
          v-if="screeningData.statusDocumentOrcids === 'NotOkay'"
          class="screeningInfoFieldBody"
        >
          <ul
            v-html="
              t('plugins.generic.scieloScreening.info.documentOrcidsNotOkay.body')
            "
          ></ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";

const { useLocalize } = pkp.modules.useLocalize;
const { useUrl } = pkp.modules.useUrl;
const { useFetch } = pkp.modules.useFetch;

const { t } = useLocalize();

const props = defineProps({
  submission: {
    type: Object,
    required: true,
  },
});

const screeningData = ref({});
const isLoading = ref(true);
const error = ref(null);

const { apiUrl } = useUrl(`submissions/${props.submission.id}/screening`);
const { data, fetch: fetchScreening } = useFetch(apiUrl);

async function loadScreeningData() {
  isLoading.value = true;
  error.value = null;
  try {
    await fetchScreening();
    screeningData.value = data.value || {};
  } catch (e) {
    error.value = "Failed to load screening data";
  } finally {
    isLoading.value = false;
  }
}

function getDocumentOrcidsStatusClass(status) {
  if (status === "Okay") {
    return "screeningStatusOkay";
  }
  if (status?.includes("Unable")) {
    return "screeningStatusNotOkay";
  }
  return "screeningStatusWarning";
}

onMounted(() => {
  loadScreeningData();
});

watch(
  () => props.submission.id,
  () => {
    loadScreeningData();
  }
);
</script>

<style scoped>
.screeningInfoArea {
  padding: 1rem;
}

.screeningInfoHeader {
  margin-bottom: 1.5rem;
}

.screeningInfoHeader h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
}

.screeningInfoHeader p {
  margin: 0;
  color: #666;
}

.screeningInfoLoading {
  display: flex;
  justify-content: center;
  padding: 2rem;
}

.screeningInfoError {
  color: #d00;
  padding: 1rem;
  background: #fee;
  border-radius: 4px;
}

.screeningInfoFields {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.screeningInfoField {
  border: 1px solid #ddd;
  border-radius: 4px;
  overflow: hidden;
}

.screeningInfoFieldHeader {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  background: #f9f9f9;
}

.screeningInfoFieldBody {
  padding: 0.75rem 1rem;
  background: #fff;
  border-top: 1px solid #ddd;
}

.screeningInfoFieldBody ul {
  margin: 0;
  padding-left: 1.5rem;
  list-style-type: disc;
}

.screeningInfoFieldBody li,
.screeningInfoFieldBody :deep(li) {
  margin: 0.25rem 0;
  list-style-type: disc;
}

.screeningStatusOkay,
.screeningStatusNotOkay,
.screeningStatusWarning {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.screeningStatusOkay {
  background-color: #28a745;
}

.screeningStatusNotOkay {
  background-color: #dc3545;
}

.screeningStatusWarning {
  background-color: #ffc107;
}
</style>
