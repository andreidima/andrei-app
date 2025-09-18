<template>
  <div :class="rootClasses" ref="root">
    <template v-if="isSingle">
      <input
        type="text"
        class="form-control rounded-3"
        :name="inputName"
        v-model="singleValue"
        :placeholder="placeholder"
        autocomplete="off"
        @focus="openSingle"
        @input="onSingleInput"
        @keydown="onSingleKeydown"
      >
      <div
        v-if="showSingleList && displayedSingleOptions.length"
        class="list-group position-absolute start-0 end-0 mt-1 shadow-sm"
        style="z-index: 1021; max-height: 240px; overflow-y: auto;"
      >
        <button
          v-for="(option, index) in displayedSingleOptions"
          :key="option + index"
          type="button"
          class="list-group-item list-group-item-action"
          :class="{ active: index === singleActiveIndex }"
          @mousedown.prevent="selectSingle(option)"
          @mouseenter="singleActiveIndex = index"
        >
          {{ option }}
        </button>
      </div>
    </template>
    <template v-else>
      <div :class="aplicatieColumnClasses" class="position-relative" ref="aplicatieWrapper">
        <input
          type="text"
          class="form-control rounded-3"
          :name="aplicatieInputName"
          v-model="aplicatieValue"
          :placeholder="placeholders.aplicatie"
          autocomplete="off"
          @focus="openAplicatie"
          @input="onAplicatieInput"
          @keydown="onAplicatieKeydown"
        >
        <div
          v-if="showAplicatieList && displayedAplicatii.length"
          class="list-group position-absolute start-0 end-0 mt-1 shadow-sm"
          style="z-index: 1021; max-height: 240px; overflow-y: auto;"
        >
          <button
            v-for="(option, index) in displayedAplicatii"
            :key="option.id"
            type="button"
            class="list-group-item list-group-item-action"
            :class="{ active: index === aplicatieActiveIndex }"
            @mousedown.prevent="selectAplicatie(option)"
            @mouseenter="aplicatieActiveIndex = index"
          >
            {{ option.nume }}
          </button>
        </div>
      </div>
      <div :class="actualizareColumnClasses" class="position-relative" ref="actualizareWrapper">
        <input
          type="text"
          class="form-control rounded-3"
          :name="actualizareInputName"
          v-model="actualizareValue"
          :placeholder="placeholders.actualizare"
          autocomplete="off"
          @focus="openActualizare"
          @input="onActualizareInput"
          @keydown="onActualizareKeydown"
        >
        <input
          v-if="actualizareIdInputName"
          type="hidden"
          :name="actualizareIdInputName"
          :value="actualizareIdValue"
        >
        <div
          v-if="showActualizareList && displayedActualizari.length"
          class="list-group position-absolute start-0 end-0 mt-1 shadow-sm"
          style="z-index: 1021; max-height: 240px; overflow-y: auto;"
        >
          <button
            v-for="(option, index) in displayedActualizari"
            :key="option.id"
            type="button"
            class="list-group-item list-group-item-action"
            :class="{ active: index === actualizareActiveIndex }"
            @mousedown.prevent="selectActualizare(option)"
            @mouseenter="actualizareActiveIndex = index"
          >
            <span class="d-block fw-semibold">{{ option.aplicatie_nume }}</span>
            <span class="d-block small text-muted">{{ option.nume }}</span>
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';

const normalize = (value) => {
  if (!value) {
    return '';
  }
  return value
    .toString()
    .normalize('NFD')
    .replace(/\p{Diacritic}/gu, '')
    .toLowerCase();
};

const toStringSafe = (value) => {
  if (value === null || value === undefined) {
    return '';
  }
  return String(value);
};

export default {
  name: 'LinkedAutocomplete',
  props: {
    mode: {
      type: String,
      default: 'single',
    },
    inputName: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: '',
    },
    options: {
      type: Array,
      default: () => [],
    },
    limit: {
      type: Number,
      default: null,
    },
    aplicatieInputName: {
      type: String,
      default: '',
    },
    actualizareInputName: {
      type: String,
      default: '',
    },
    actualizareIdInputName: {
      type: String,
      default: '',
    },
    initialValue: {
      type: String,
      default: '',
    },
    initialAplicatie: {
      type: String,
      default: '',
    },
    initialActualizare: {
      type: String,
      default: '',
    },
    initialActualizareId: {
      type: [String, Number],
      default: '',
    },
    aplicatii: {
      type: Array,
      default: () => [],
    },
    actualizari: {
      type: Array,
      default: () => [],
    },
    placeholders: {
      type: Object,
      default: () => ({
        aplicatie: 'Aplicație',
        actualizare: 'Actualizare',
      }),
    },
    columnClasses: {
      type: Object,
      default: () => ({
        aplicatie: 'col-12 col-lg-6',
        actualizare: 'col-12 col-lg-6',
      }),
    },
  },
  setup(props) {
    const isSingleMode = (props.mode || 'single').toLowerCase() === 'single';
    const root = ref(null);
    const suggestionsLimit = computed(() => {
      const parsed = Number(props.limit);
      return Number.isFinite(parsed) && parsed > 0 ? Math.floor(parsed) : Infinity;
    });

    const rootClasses = computed(() => {
      return isSingleMode ? 'position-relative w-100' : 'row g-2 w-100';
    });

    // Single field logic
    const singleValue = ref(props.initialValue || '');
    const showSingleList = ref(false);
    const singleActiveIndex = ref(-1);

    const sanitizedSingleOptions = computed(() => {
      if (!Array.isArray(props.options)) {
        return [];
      }
      return props.options
        .map((option) => (option === null || option === undefined ? '' : option.toString()))
        .filter((option) => option.length);
    });

    const filteredSingleOptions = computed(() => {
      const query = normalize(singleValue.value);
      if (!query) {
        return sanitizedSingleOptions.value;
      }
      return sanitizedSingleOptions.value.filter((option) => normalize(option).includes(query));
    });

    const displayedSingleOptions = computed(() => filteredSingleOptions.value.slice(0, suggestionsLimit.value));

    const openSingle = () => {
      showSingleList.value = true;
    };

    const onSingleInput = () => {
      openSingle();
      singleActiveIndex.value = -1;
    };

    const hideSingle = () => {
      showSingleList.value = false;
      singleActiveIndex.value = -1;
    };

    const selectSingle = (option) => {
      singleValue.value = option;
      hideSingle();
    };

    const onSingleKeydown = (event) => {
      if (!isSingleMode) {
        return;
      }
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        openSingle();
        if (!displayedSingleOptions.value.length) {
          return;
        }
        singleActiveIndex.value = (singleActiveIndex.value + 1) % displayedSingleOptions.value.length;
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        openSingle();
        if (!displayedSingleOptions.value.length) {
          return;
        }
        if (singleActiveIndex.value <= 0) {
          singleActiveIndex.value = displayedSingleOptions.value.length - 1;
        } else {
          singleActiveIndex.value -= 1;
        }
      } else if (event.key === 'Enter') {
        if (singleActiveIndex.value >= 0) {
          event.preventDefault();
          const selected = displayedSingleOptions.value[singleActiveIndex.value];
          if (selected !== undefined) {
            selectSingle(selected);
          }
        }
      } else if (event.key === 'Escape') {
        hideSingle();
      }
    };

    // Linked fields logic
    const aplicatieValue = ref(props.initialAplicatie || '');
    const actualizareValue = ref(props.initialActualizare || '');
    const actualizareIdValue = ref(toStringSafe(props.initialActualizareId));
    const selectedAplicatieId = ref('');
    const selectedActualizare = ref(null);

    const showAplicatieList = ref(false);
    const showActualizareList = ref(false);
    const aplicatieActiveIndex = ref(-1);
    const actualizareActiveIndex = ref(-1);

    const sanitizedAplicatii = computed(() => {
      if (!Array.isArray(props.aplicatii)) {
        return [];
      }
      return props.aplicatii.map((app) => ({
        id: toStringSafe(app.id),
        nume: app?.nume ?? '',
      }));
    });

    const sanitizedActualizari = computed(() => {
      if (!Array.isArray(props.actualizari)) {
        return [];
      }
      return props.actualizari.map((item) => ({
        id: toStringSafe(item.id),
        nume: item?.nume ?? '',
        aplicatie_id: toStringSafe(item?.aplicatie_id),
        aplicatie_nume: item?.aplicatie_nume ?? '',
      }));
    });

    const actualizariByAplicatie = computed(() => {
      const map = new Map();
      sanitizedActualizari.value.forEach((item) => {
        const list = map.get(item.aplicatie_id) || [];
        list.push(item);
        map.set(item.aplicatie_id, list);
      });
      return map;
    });

    const filteredAplicatii = computed(() => {
      const aplicatieQuery = normalize(aplicatieValue.value);
      const actualizareQuery = normalize(actualizareValue.value);
      const selectedActualizareData = selectedActualizare.value;

      return sanitizedAplicatii.value.filter((app) => {
        const matchesAplicatie = !aplicatieQuery || normalize(app.nume).includes(aplicatieQuery);
        if (!matchesAplicatie) {
          return false;
        }

        if (selectedActualizareData) {
          return selectedActualizareData.aplicatie_id === app.id;
        }

        if (actualizareQuery) {
          const list = actualizariByAplicatie.value.get(app.id) || [];
          return list.some((item) => normalize(item.nume).includes(actualizareQuery));
        }

        return true;
      });
    });

    const filteredActualizari = computed(() => {
      const aplicatieQuery = normalize(aplicatieValue.value);
      const actualizareQuery = normalize(actualizareValue.value);
      const selectedAppId = selectedAplicatieId.value;

      return sanitizedActualizari.value.filter((item) => {
        if (selectedAppId && item.aplicatie_id !== selectedAppId) {
          return false;
        }
        if (!selectedAppId && aplicatieQuery && !normalize(item.aplicatie_nume).includes(aplicatieQuery)) {
          return false;
        }
        if (actualizareQuery && !normalize(item.nume).includes(actualizareQuery)) {
          return false;
        }
        return true;
      });
    });

    const displayedAplicatii = computed(() => filteredAplicatii.value.slice(0, suggestionsLimit.value));
    const displayedActualizari = computed(() => filteredActualizari.value.slice(0, suggestionsLimit.value));

    const placeholders = computed(() => ({
      aplicatie: props.placeholders?.aplicatie ?? 'Aplicație',
      actualizare: props.placeholders?.actualizare ?? 'Actualizare',
    }));

    const aplicatieColumnClasses = computed(() => props.columnClasses?.aplicatie ?? 'col-12 col-lg-6');
    const actualizareColumnClasses = computed(() => props.columnClasses?.actualizare ?? 'col-12 col-lg-6');

    const openAplicatie = () => {
      showAplicatieList.value = true;
    };

    const openActualizare = () => {
      showActualizareList.value = true;
    };

    const hideAplicatie = () => {
      showAplicatieList.value = false;
      aplicatieActiveIndex.value = -1;
    };

    const hideActualizare = () => {
      showActualizareList.value = false;
      actualizareActiveIndex.value = -1;
    };

    const selectAplicatie = (app) => {
      selectedAplicatieId.value = app.id;
      aplicatieValue.value = app.nume;
      hideAplicatie();
      if (selectedActualizare.value && selectedActualizare.value.aplicatie_id !== app.id) {
        selectedActualizare.value = null;
        actualizareValue.value = '';
        actualizareIdValue.value = '';
      }
    };

    const selectActualizare = (item) => {
      selectedActualizare.value = item;
      actualizareValue.value = item.nume;
      actualizareIdValue.value = item.id;
      if (item.aplicatie_id) {
        selectedAplicatieId.value = item.aplicatie_id;
        const matchedAplicatie = sanitizedAplicatii.value.find((app) => app.id === item.aplicatie_id);
        aplicatieValue.value = matchedAplicatie?.nume ?? item.aplicatie_nume ?? '';
      }
      hideActualizare();
      hideAplicatie();
    };

    const moveIndex = (currentIndex, collection, increment = 1) => {
      if (!collection.length) {
        return -1;
      }
      const next = (currentIndex + increment + collection.length) % collection.length;
      return next;
    };

    const onAplicatieInput = () => {
      openAplicatie();
      aplicatieActiveIndex.value = -1;
    };

    const onActualizareInput = () => {
      openActualizare();
      actualizareActiveIndex.value = -1;
    };

    const onAplicatieKeydown = (event) => {
      if (isSingleMode) {
        return;
      }
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        openAplicatie();
        aplicatieActiveIndex.value = moveIndex(aplicatieActiveIndex.value, displayedAplicatii.value, 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        openAplicatie();
        aplicatieActiveIndex.value = moveIndex(aplicatieActiveIndex.value, displayedAplicatii.value, -1);
      } else if (event.key === 'Enter') {
        if (aplicatieActiveIndex.value >= 0) {
          event.preventDefault();
          const selected = displayedAplicatii.value[aplicatieActiveIndex.value];
          if (selected) {
            selectAplicatie(selected);
          }
        }
      } else if (event.key === 'Escape') {
        hideAplicatie();
      }
    };

    const onActualizareKeydown = (event) => {
      if (isSingleMode) {
        return;
      }
      if (event.key === 'ArrowDown') {
        event.preventDefault();
        openActualizare();
        actualizareActiveIndex.value = moveIndex(actualizareActiveIndex.value, displayedActualizari.value, 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        openActualizare();
        actualizareActiveIndex.value = moveIndex(actualizareActiveIndex.value, displayedActualizari.value, -1);
      } else if (event.key === 'Enter') {
        if (actualizareActiveIndex.value >= 0) {
          event.preventDefault();
          const selected = displayedActualizari.value[actualizareActiveIndex.value];
          if (selected) {
            selectActualizare(selected);
          }
        }
      } else if (event.key === 'Escape') {
        hideActualizare();
      }
    };

    const initializeSelections = () => {
      let initialized = false;
      const initialActualizareId = toStringSafe(props.initialActualizareId);
      if (initialActualizareId) {
        const matchById = sanitizedActualizari.value.find((item) => item.id === initialActualizareId);
        if (matchById) {
          selectActualizare(matchById);
          initialized = true;
        }
      }
      if (!initialized && props.initialActualizare) {
        const target = sanitizedActualizari.value.find((item) => normalize(item.nume) === normalize(props.initialActualizare));
        if (target) {
          selectActualizare(target);
          initialized = true;
        }
      }
      if (!initialized && props.initialAplicatie) {
        const appMatch = sanitizedAplicatii.value.find((app) => normalize(app.nume) === normalize(props.initialAplicatie));
        if (appMatch) {
          selectAplicatie(appMatch);
        }
      }
    };

    let hasInitialized = false;
    watch([
      sanitizedAplicatii,
      sanitizedActualizari,
    ], () => {
      if (!isSingleMode && !hasInitialized) {
        initializeSelections();
        hasInitialized = true;
      }
    }, { immediate: true });

    watch(aplicatieValue, (newValue) => {
      if (isSingleMode) {
        return;
      }
      if (!newValue) {
        selectedAplicatieId.value = '';
        if (!showActualizareList.value) {
          selectedActualizare.value = null;
          actualizareIdValue.value = '';
        }
        return;
      }
      const selectedApp = sanitizedAplicatii.value.find((app) => app.id === selectedAplicatieId.value);
      if (selectedApp && normalize(selectedApp.nume) !== normalize(newValue)) {
        selectedAplicatieId.value = '';
        if (!showActualizareList.value) {
          selectedActualizare.value = null;
          actualizareIdValue.value = '';
        }
      }
    });

    watch(actualizareValue, (newValue) => {
      if (isSingleMode) {
        return;
      }
      if (!newValue) {
        selectedActualizare.value = null;
        actualizareIdValue.value = '';
        return;
      }
      if (selectedActualizare.value && normalize(selectedActualizare.value.nume) !== normalize(newValue)) {
        selectedActualizare.value = null;
        actualizareIdValue.value = '';
      }
    });

    const handleDocumentClick = (event) => {
      if (!root.value || root.value.contains(event.target)) {
        return;
      }
      if (isSingleMode) {
        hideSingle();
      } else {
        hideAplicatie();
        hideActualizare();
      }
    };

    onMounted(() => {
      document.addEventListener('mousedown', handleDocumentClick);
    });

    onBeforeUnmount(() => {
      document.removeEventListener('mousedown', handleDocumentClick);
    });

    return {
      root,
      isSingle: isSingleMode,
      rootClasses,
      // single
      inputName: props.inputName,
      placeholder: props.placeholder,
      singleValue,
      showSingleList,
      displayedSingleOptions,
      singleActiveIndex,
      openSingle,
      onSingleInput,
      onSingleKeydown,
      selectSingle,
      // linked
      aplicatieInputName: props.aplicatieInputName,
      actualizareInputName: props.actualizareInputName,
      actualizareIdInputName: props.actualizareIdInputName,
      aplicatieValue,
      actualizareValue,
      actualizareIdValue,
      placeholders,
      aplicatieColumnClasses,
      actualizareColumnClasses,
      showAplicatieList,
      showActualizareList,
      displayedAplicatii,
      displayedActualizari,
      aplicatieActiveIndex,
      actualizareActiveIndex,
      openAplicatie,
      openActualizare,
      onAplicatieInput,
      onActualizareInput,
      onAplicatieKeydown,
      onActualizareKeydown,
      selectAplicatie,
      selectActualizare,
    };
  },
};
</script>

<style scoped>
.list-group-item-action.active {
  background-color: var(--bs-primary);
  border-color: var(--bs-primary);
}
</style>
