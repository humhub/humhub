/*!
 * AUTO-GENERATED FILE — do not edit.
 * Compiled from marketplace/vue/ via `grunt build-vue --module=marketplace`.
 * See docs/develop/ui-js-vuejs.md
 */
(function(vue, vue$1) {
  "use strict";
  const _export_sfc = (sfc, props) => {
    const target = sfc.__vccOpts || sfc;
    for (const [key, val] of props) {
      target[key] = val;
    }
    return target;
  };
  const _sfc_main$5 = {
    name: "CoreVersionInfo",
    props: {
      latest: { type: String, required: true },
      updateUrl: { type: String, default: null }
    },
    computed: {
      message() {
        return vue.i18n.t("MarketplaceModule.base", "A new update is available (HumHub %version%)!").replace("%version%", this.latest);
      },
      learnMore() {
        return vue.i18n.t("MarketplaceModule.base", "Learn more");
      }
    }
  };
  const _hoisted_1$4 = {
    class: "c-marketplace-notice",
    role: "status"
  };
  const _hoisted_2$4 = { class: "c-marketplace-notice__text" };
  const _hoisted_3$4 = ["href"];
  function _sfc_render$5(_ctx, _cache, $props, $setup, $data, $options) {
    return vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1$4, [
      _cache[0] || (_cache[0] = vue$1.createElementVNode(
        "i",
        {
          class: "ti ti-info-circle c-marketplace-notice__icon",
          "aria-hidden": "true"
        },
        null,
        -1
        /* CACHED */
      )),
      vue$1.createElementVNode(
        "span",
        _hoisted_2$4,
        vue$1.toDisplayString($options.message),
        1
        /* TEXT */
      ),
      $props.updateUrl ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
        key: 0,
        href: $props.updateUrl,
        class: "btn btn-sm btn-accent c-marketplace-notice__action"
      }, vue$1.toDisplayString($options.learnMore), 9, _hoisted_3$4)) : vue$1.createCommentVNode("v-if", true)
    ]);
  }
  const CoreVersionInfo = /* @__PURE__ */ _export_sfc(_sfc_main$5, [["render", _sfc_render$5]]);
  const _sfc_main$4 = {
    name: "RichText",
    props: {
      parts: { type: Array, required: true },
      flag: { type: String, default: "strong" },
      tag: { type: String, default: "b" },
      markClass: { type: String, default: null }
    }
  };
  function _sfc_render$4(_ctx, _cache, $props, $setup, $data, $options) {
    return vue$1.openBlock(true), vue$1.createElementBlock(
      vue$1.Fragment,
      null,
      vue$1.renderList($props.parts, (part, index) => {
        return vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: index },
          [
            part[$props.flag] ? (vue$1.openBlock(), vue$1.createBlock(vue$1.resolveDynamicComponent($props.tag), {
              key: 0,
              class: vue$1.normalizeClass($props.markClass)
            }, {
              default: vue$1.withCtx(() => [
                vue$1.createTextVNode(
                  vue$1.toDisplayString(part.text),
                  1
                  /* TEXT */
                )
              ]),
              _: 2
              /* DYNAMIC */
            }, 1032, ["class"])) : (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 1 },
              [
                vue$1.createTextVNode(
                  vue$1.toDisplayString(part.text),
                  1
                  /* TEXT */
                )
              ],
              64
              /* STABLE_FRAGMENT */
            ))
          ],
          64
          /* STABLE_FRAGMENT */
        );
      }),
      128
      /* KEYED_FRAGMENT */
    );
  }
  const RichText = /* @__PURE__ */ _export_sfc(_sfc_main$4, [["render", _sfc_render$4]]);
  const moduleId = (id) => encodeURIComponent(id);
  const payload = (response) => response && response.response && typeof response.response === "object" ? response.response : response;
  const installModule = (id) => vue.client.post(vue.apiUrl(`marketplace/module/${moduleId(id)}/install`)).then(payload);
  const updateModule = (id) => vue.client.post(vue.apiUrl(`marketplace/module/${moduleId(id)}/update`)).then(payload);
  const enableModule = (id) => vue.client.post(vue.apiUrl(`module/${moduleId(id)}/enable`)).then(payload);
  const fetchModules = (params) => vue.client.get(vue.apiUrl("marketplace/module", { pageSize: 100, ...params })).then((response) => payload(response).results || []);
  const fetchCoreVersion = () => vue.client.get(vue.apiUrl("marketplace/core-version")).then(payload);
  const saveSettings = (data) => vue.client.patch(vue.apiUrl("marketplace/settings"), { data }).then(payload).then((settings) => ({
    includeBetaUpdates: settings.includeBetaUpdates === true,
    includeCommunityModules: settings.includeCommunityModules === true
  }));
  const registerLicenceKey = (licenceKey) => vue.client.post(vue.apiUrl("marketplace/licence-key"), { data: { licenceKey } }).then(payload);
  const errorMessage = (response, fallback) => {
    const errors = response && response.errors;
    if (errors && typeof errors === "object") {
      const first = Object.values(errors)[0];
      if (Array.isArray(first) && first.length) {
        return String(first[0]);
      }
    }
    if (response && typeof response.message === "string" && response.message !== "") {
      return response.message;
    }
    return fallback;
  };
  const communityWarning = () => vue.i18n.t("MarketplaceModule.base", "Community modules are developed by third parties and are <strong>not tested or maintained by the HumHub team</strong>.<br><br>They may not be compatible with your HumHub version, can cause <strong>instability or unexpected behavior</strong>, and may stop working after future updates. Their long-term maintenance is not guaranteed.<br><br>Only enable this option if you understand the risks and trust the source of the module you intend to install.");
  const communityAcknowledge = () => vue.i18n.t("MarketplaceModule.base", "I understand the risk and want to continue.");
  const badgeLabel = (badge) => ({
    professional: vue.i18n.t("MarketplaceModule.base", "Professional Edition"),
    official: vue.i18n.t("MarketplaceModule.base", "Official"),
    partner: vue.i18n.t("MarketplaceModule.base", "Partner"),
    deprecated: vue.i18n.t("MarketplaceModule.base", "Deprecated"),
    community: vue.i18n.t("MarketplaceModule.base", "Community")
  })[badge] || "";
  const priceText = (price) => price && !price.onRequest && price.amount !== null ? `${price.amount}€` : "";
  const buyLabel = (price) => {
    const amount = priceText(price);
    return amount === "" ? vue.i18n.t("MarketplaceModule.base", "Buy") : vue.i18n.t("MarketplaceModule.base", "Buy (%price%)").replace("%price%", amount);
  };
  const MARK = "";
  const MARKED = /\u0001(\w+)\u0001/;
  const emphasis = (...keys) => Object.fromEntries(keys.map((key) => [key, `${MARK}${key}${MARK}`]));
  const strongParts = (translated, values) => String(translated).split(MARKED).map((part, index) => index % 2 === 1 ? { text: String(values[part] ?? ""), strong: true } : { text: part, strong: false }).filter((part) => part.text !== "");
  const thirdPartyNotice = () => [
    vue.i18n.t("MarketplaceModule.base", "This Module was developed by a third-party."),
    vue.i18n.t("MarketplaceModule.base", "The HumHub project does not guarantee the functionality, quality or the continuous development of this Module."),
    vue.i18n.t("MarketplaceModule.base", "Third-party Modules are not covered by Professional Edition agreements.")
  ];
  const communityNotice = () => vue.i18n.t("MarketplaceModule.base", 'If this Module is additionally marked as <strong>"Unverified Community"</strong> it is neither tested nor maintained by the HumHub project team. It may cause instability or stop working after future updates.');
  const STEP_MS = 300;
  const PROGRESS_TICK_MS = 120;
  const PROGRESS_LIMIT = 0.9;
  const PROGRESS_TAU_MS = 2500;
  const STEPS = ["buy", "license", "confirm", "installing", "activate", "configure", "failed"];
  const isDefinite = (response) => Boolean(response) && response.status >= 400 && response.status < 500;
  const reducedMotion$1 = () => typeof window.matchMedia === "function" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  let uid = 0;
  const _sfc_main$3 = {
    name: "InstallDialog",
    components: { RichText },
    props: {
      module: { type: Object, required: true },
      initialStep: {
        type: String,
        default: "confirm",
        validator: (value) => ["buy", "confirm"].includes(value)
      }
    },
    emits: ["registered", "installed", "activated", "close"],
    data() {
      return {
        step: this.initialStep,
        current: this.module,
        licenceKey: "",
        keyError: null,
        checking: false,
        error: null,
        progress: 0,
        activating: false,
        inputId: `marketplace-install-licence-${++uid}`
      };
    },
    computed: {
      installing() {
        return this.step === "installing";
      },
      // A request runs whose outcome the dialog still has to present: it cannot be closed.
      locked() {
        return this.installing || this.checking || this.activating;
      },
      // The note belongs to the step the dialog opened on.
      showNotice() {
        return this.module.isThirdParty === true && this.step === this.initialStep;
      },
      version() {
        return this.module.latestCompatibleVersion || this.module.latestVersion || "";
      },
      title() {
        return {
          buy: vue.i18n.t("MarketplaceModule.base", "Buy Module"),
          activate: vue.i18n.t("MarketplaceModule.base", "Activate Module"),
          configure: vue.i18n.t("MarketplaceModule.base", "Configure Module")
        }[this.step] || vue.i18n.t("MarketplaceModule.base", "Install Module");
      },
      texts() {
        const values = {
          moduleName: this.module.name,
          buyLicenseKey: this.labels.buyLicenceKey,
          addLicense: this.labels.addLicence
        };
        return {
          buy: strongParts(vue.i18n.t("MarketplaceModule.base", "To install {moduleName}, you need a License Key. Buy one with {buyLicenseKey}, or choose {addLicense} if you already have one.", emphasis("moduleName", "buyLicenseKey", "addLicense")), values),
          license: strongParts(vue.i18n.t("MarketplaceModule.base", "To install {moduleName}, enter the License Key you purchased.", emphasis("moduleName")), values),
          installing: strongParts(vue.i18n.t("MarketplaceModule.base", "Installing {moduleName}...", emphasis("moduleName")), values),
          activate: strongParts(vue.i18n.t("MarketplaceModule.base", "You are all done! {moduleName} was installed. To make it available on your network, activate it now or later in Module Administration.", emphasis("moduleName")), values),
          configure: strongParts(vue.i18n.t("MarketplaceModule.base", "{moduleName} was activated! Configure it now or later in Module Administration.", emphasis("moduleName")), values)
        };
      },
      labels() {
        return {
          buyLicenceKey: vue.i18n.t("MarketplaceModule.base", "Buy License Key"),
          addLicence: vue.i18n.t("MarketplaceModule.base", "Add License"),
          newTab: vue.i18n.t("MarketplaceModule.base", "(opens in a new tab)"),
          licenceKey: vue.i18n.t("MarketplaceModule.base", "License Key"),
          placeholder: "XXXX-XXXX-XXXX-XXXX",
          install: vue.i18n.t("MarketplaceModule.base", "Install"),
          installing: vue.i18n.t("MarketplaceModule.base", "Installing {moduleName}...", { moduleName: this.module.name }),
          installingButton: vue.i18n.t("MarketplaceModule.base", "Installing"),
          activate: vue.i18n.t("MarketplaceModule.base", "Activate"),
          configure: vue.i18n.t("MarketplaceModule.base", "Configure"),
          cancel: vue.i18n.t("MarketplaceModule.base", "Cancel"),
          back: vue.i18n.t("base", "Back"),
          close: vue.i18n.t("base", "Close"),
          reloadBeforeRetry: vue.i18n.t("MarketplaceModule.base", "The installation did not finish. Please reload the page before trying again."),
          thirdParty: thirdPartyNotice(),
          community: communityNotice()
        };
      }
    },
    watch: {
      // Runs before the re-render: the old step's height is measured here, the new one's after.
      step() {
        const content = this.content();
        const from = content ? content.offsetHeight : 0;
        this.$nextTick(() => {
          this.easeHeight(content, from);
          this.focusStep();
        });
      }
    },
    beforeUnmount() {
      this.destroyed = true;
      this.stopProgress();
    },
    methods: {
      content() {
        return this.$refs.root ? this.$refs.root.closest(".modal-content") : null;
      },
      easeHeight(content, from) {
        if (!content || from <= 0 || reducedMotion$1() || typeof content.animate !== "function") {
          return;
        }
        const to = content.offsetHeight;
        if (to === from) {
          return;
        }
        content.style.overflow = "hidden";
        const motion = content.animate([{ height: `${from}px` }, { height: `${to}px` }], { duration: STEP_MS, easing: "ease" });
        const done = () => {
          content.style.overflow = "";
        };
        motion.onfinish = done;
        motion.oncancel = done;
      },
      // Focus follows the step, so it is never left on a control that just went away.
      focusStep() {
        if (this.step === "license" && this.$refs.input) {
          this.$refs.input.focus();
        } else if (this.$refs.primary && !this.$refs.primary.disabled) {
          this.$refs.primary.focus();
        } else if (this.$refs.root) {
          const dialog = this.$refs.root.closest(".modal");
          if (dialog) {
            dialog.focus();
          }
        }
      },
      go(step) {
        if (STEPS.includes(step)) {
          this.error = null;
          this.step = step;
        }
      },
      onShow(show) {
        if (!show) {
          this.close();
        }
      },
      close() {
        if (!this.locked) {
          this.$emit("close");
        }
      },
      register() {
        const key = this.licenceKey.trim();
        if (key === "" || this.checking) {
          return;
        }
        this.checking = true;
        this.keyError = null;
        registerLicenceKey(key).then(() => {
          if (this.destroyed) {
            return;
          }
          this.checking = false;
          this.$emit("registered");
          this.install(true);
        }, (response) => {
          if (this.destroyed) {
            return;
          }
          this.checking = false;
          this.keyError = errorMessage(response, vue.i18n.t("MarketplaceModule.base", "Invalid module license key!"));
          vue.log.error(response);
        });
      },
      startProgress() {
        this.stopProgress();
        this.progress = 0;
        const started = Date.now();
        this.progressTimer = setInterval(() => {
          this.progress = PROGRESS_LIMIT * (1 - Math.exp(-(Date.now() - started) / PROGRESS_TAU_MS));
        }, PROGRESS_TICK_MS);
      },
      stopProgress() {
        clearInterval(this.progressTimer);
        this.progressTimer = null;
      },
      // `registered`: a key was registered just before, for this module.
      async install(registered = false) {
        if (this.installing) {
          return;
        }
        this.error = null;
        this.step = "installing";
        this.startProgress();
        let installed;
        try {
          installed = await installModule(this.module.id);
        } catch (response) {
          if (this.destroyed) {
            return;
          }
          vue.log.error(response);
          installed = isDefinite(response) ? null : await this.reread();
          if (this.destroyed) {
            return;
          }
          if (!installed) {
            this.installFailed(response, registered);
            return;
          }
        }
        if (this.destroyed) {
          return;
        }
        this.stopProgress();
        this.progress = 1;
        if (!reducedMotion$1()) {
          await new Promise((resolve) => {
            setTimeout(resolve, STEP_MS);
          });
          if (this.destroyed) {
            return;
          }
        }
        this.current = installed;
        this.$emit("installed", installed);
        vue.status("success", vue.i18n.t("MarketplaceModule.base", "{moduleName} installed successfully.", { moduleName: this.module.name }));
        this.step = "activate";
      },
      // The module's record after an install without a definite answer, if it is installed
      // now; null otherwise (or when it cannot be read either).
      async reread() {
        try {
          const [found] = (await fetchModules({ id: this.module.id })).filter((module) => module.id === this.module.id);
          return found && found.installedVersion !== null && found.installedVersion !== void 0 ? found : null;
        } catch (response) {
          vue.log.error(response);
          return null;
        }
      },
      installFailed(response, registered) {
        this.stopProgress();
        const message = errorMessage(response, vue.i18n.t("MarketplaceModule.base", "Could not install the module."));
        if (!isDefinite(response)) {
          this.error = message;
          this.step = "failed";
        } else if (registered && response.status === 422) {
          this.step = "license";
          this.keyError = vue.i18n.t("MarketplaceModule.base", "This License Key does not license {moduleName}.", { moduleName: this.module.name });
        } else {
          this.error = message;
          this.step = "confirm";
        }
      },
      activate() {
        if (this.activating) {
          return;
        }
        this.activating = true;
        this.error = null;
        enableModule(this.module.id).then((result) => {
          if (this.destroyed) {
            return;
          }
          this.activating = false;
          this.current = { ...this.current, isEnabled: result.isEnabled === true, configUrl: result.configUrl || null };
          this.$emit("activated", this.current);
          if (this.current.configUrl) {
            this.step = "configure";
          } else {
            vue.status("success", vue.i18n.t("MarketplaceModule.base", "{moduleName} was activated.", { moduleName: this.module.name }));
            this.$emit("close");
          }
        }, (response) => {
          if (this.destroyed) {
            return;
          }
          this.activating = false;
          this.error = errorMessage(response, vue.i18n.t("MarketplaceModule.base", "Could not enable the module."));
          vue.log.error(response);
        });
      }
    }
  };
  const _hoisted_1$3 = ["id"];
  const _hoisted_2$3 = ["aria-label", "title", "disabled"];
  const _hoisted_3$3 = ["data-step"];
  const _hoisted_4$3 = { class: "c-install-dialog__step" };
  const _hoisted_5$3 = {
    key: 0,
    class: "c-mp-dialog__text"
  };
  const _hoisted_6$2 = { class: "c-mp-dialog__text" };
  const _hoisted_7$2 = { class: "c-install-dialog__field" };
  const _hoisted_8$2 = ["for"];
  const _hoisted_9$2 = ["id", "placeholder", "readonly", "aria-invalid", "aria-describedby"];
  const _hoisted_10$2 = {
    key: 0,
    class: "ti ti-loader c-licence-field__icon c-mp-spin",
    "aria-hidden": "true"
  };
  const _hoisted_11$2 = ["id"];
  const _hoisted_12$2 = {
    key: 2,
    class: "c-install-dialog__module"
  };
  const _hoisted_13$2 = ["src"];
  const _hoisted_14$2 = { class: "c-install-dialog__module-text" };
  const _hoisted_15$2 = { class: "c-install-dialog__name" };
  const _hoisted_16$2 = {
    key: 0,
    class: "c-install-dialog__version"
  };
  const _hoisted_17$2 = { class: "c-mp-dialog__text" };
  const _hoisted_18$2 = ["aria-label", "aria-valuenow"];
  const _hoisted_19$2 = {
    key: 4,
    class: "c-mp-dialog__text"
  };
  const _hoisted_20$2 = {
    key: 5,
    class: "c-mp-dialog__text"
  };
  const _hoisted_21$2 = {
    key: 6,
    class: "c-mp-dialog__text"
  };
  const _hoisted_22$2 = { class: "c-mp-notice__text" };
  const _hoisted_23$2 = ["innerHTML"];
  const _hoisted_24$2 = {
    key: 1,
    class: "c-mp-dialog__error",
    role: "alert"
  };
  const _hoisted_25$2 = ["href"];
  const _hoisted_26$2 = { class: "visually-hidden" };
  const _hoisted_27$2 = ["disabled"];
  const _hoisted_28$1 = ["disabled"];
  const _hoisted_29$1 = {
    key: 3,
    type: "button",
    class: "btn btn-primary c-mp-dialog__busy",
    disabled: ""
  };
  const _hoisted_30$1 = ["disabled"];
  const _hoisted_31$1 = ["disabled"];
  const _hoisted_32$1 = {
    key: 0,
    class: "ti ti-loader c-mp-spin",
    "aria-hidden": "true"
  };
  const _hoisted_33$1 = ["href"];
  function _sfc_render$3(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_RichText = vue$1.resolveComponent("RichText");
    const _component_UiModal = vue$1.resolveComponent("UiModal");
    return vue$1.openBlock(), vue$1.createBlock(_component_UiModal, {
      show: true,
      "dialog-class": "c-mp-dialog c-install-dialog",
      "backdrop-close": !$options.locked,
      keyboard: !$options.locked,
      "onUpdate:show": $options.onShow
    }, {
      header: vue$1.withCtx(({ titleId }) => [
        vue$1.createElementVNode("h5", {
          id: titleId,
          class: "modal-title"
        }, vue$1.toDisplayString($options.title), 9, _hoisted_1$3),
        vue$1.createElementVNode("button", {
          type: "button",
          class: "btn-close",
          "aria-label": $options.labels.close,
          title: $options.labels.close,
          disabled: $options.locked,
          onClick: _cache[0] || (_cache[0] = (...args) => $options.close && $options.close(...args))
        }, null, 8, _hoisted_2$3)
      ]),
      footer: vue$1.withCtx(() => [
        $data.step === "buy" ? (vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: 0 },
          [
            vue$1.createElementVNode(
              "button",
              {
                type: "button",
                class: "btn btn-secondary",
                onClick: _cache[4] || (_cache[4] = (...args) => $options.close && $options.close(...args))
              },
              vue$1.toDisplayString($options.labels.cancel),
              1
              /* TEXT */
            ),
            vue$1.createElementVNode(
              "button",
              {
                ref: "primary",
                type: "button",
                class: "btn btn-primary",
                onClick: _cache[5] || (_cache[5] = ($event) => $options.go("license"))
              },
              vue$1.toDisplayString($options.labels.addLicence),
              513
              /* TEXT, NEED_PATCH */
            )
          ],
          64
          /* STABLE_FRAGMENT */
        )) : $data.step === "license" ? (vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: 1 },
          [
            vue$1.createElementVNode("button", {
              type: "button",
              class: "btn btn-secondary",
              disabled: $data.checking,
              onClick: _cache[6] || (_cache[6] = ($event) => $options.go("buy"))
            }, vue$1.toDisplayString($options.labels.back), 9, _hoisted_27$2),
            vue$1.createElementVNode("button", {
              type: "button",
              class: "btn btn-primary",
              disabled: $data.checking || $data.licenceKey.trim() === "",
              onClick: _cache[7] || (_cache[7] = (...args) => $options.register && $options.register(...args))
            }, vue$1.toDisplayString($options.labels.install), 9, _hoisted_28$1)
          ],
          64
          /* STABLE_FRAGMENT */
        )) : $data.step === "confirm" ? (vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: 2 },
          [
            vue$1.createElementVNode(
              "button",
              {
                type: "button",
                class: "btn btn-secondary",
                onClick: _cache[8] || (_cache[8] = (...args) => $options.close && $options.close(...args))
              },
              vue$1.toDisplayString($options.labels.cancel),
              1
              /* TEXT */
            ),
            vue$1.createElementVNode(
              "button",
              {
                ref: "primary",
                type: "button",
                class: "btn btn-primary",
                onClick: _cache[9] || (_cache[9] = ($event) => $options.install())
              },
              vue$1.toDisplayString($options.labels.install),
              513
              /* TEXT, NEED_PATCH */
            )
          ],
          64
          /* STABLE_FRAGMENT */
        )) : $data.step === "installing" ? (vue$1.openBlock(), vue$1.createElementBlock("button", _hoisted_29$1, [
          _cache[15] || (_cache[15] = vue$1.createElementVNode(
            "i",
            {
              class: "ti ti-loader c-mp-spin",
              "aria-hidden": "true"
            },
            null,
            -1
            /* CACHED */
          )),
          vue$1.createTextVNode(
            vue$1.toDisplayString($options.labels.installingButton),
            1
            /* TEXT */
          )
        ])) : $data.step === "activate" ? (vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: 4 },
          [
            vue$1.createElementVNode("button", {
              type: "button",
              class: "btn btn-secondary",
              disabled: $data.activating,
              onClick: _cache[10] || (_cache[10] = (...args) => $options.close && $options.close(...args))
            }, vue$1.toDisplayString($options.labels.close), 9, _hoisted_30$1),
            vue$1.createElementVNode("button", {
              ref: "primary",
              type: "button",
              class: "btn btn-primary c-mp-dialog__busy",
              disabled: $data.activating,
              onClick: _cache[11] || (_cache[11] = (...args) => $options.activate && $options.activate(...args))
            }, [
              $data.activating ? (vue$1.openBlock(), vue$1.createElementBlock("i", _hoisted_32$1)) : vue$1.createCommentVNode("v-if", true),
              vue$1.createTextVNode(
                vue$1.toDisplayString($options.labels.activate),
                1
                /* TEXT */
              )
            ], 8, _hoisted_31$1)
          ],
          64
          /* STABLE_FRAGMENT */
        )) : $data.step === "configure" ? (vue$1.openBlock(), vue$1.createElementBlock(
          vue$1.Fragment,
          { key: 5 },
          [
            vue$1.createElementVNode(
              "button",
              {
                type: "button",
                class: "btn btn-secondary",
                onClick: _cache[12] || (_cache[12] = (...args) => $options.close && $options.close(...args))
              },
              vue$1.toDisplayString($options.labels.close),
              1
              /* TEXT */
            ),
            vue$1.createElementVNode("a", {
              ref: "primary",
              class: "btn btn-primary",
              href: $data.current.configUrl
            }, vue$1.toDisplayString($options.labels.configure), 9, _hoisted_33$1)
          ],
          64
          /* STABLE_FRAGMENT */
        )) : $data.step === "failed" ? (vue$1.openBlock(), vue$1.createElementBlock(
          "button",
          {
            key: 6,
            ref: "primary",
            type: "button",
            class: "btn btn-secondary",
            onClick: _cache[13] || (_cache[13] = (...args) => $options.close && $options.close(...args))
          },
          vue$1.toDisplayString($options.labels.close),
          513
          /* TEXT, NEED_PATCH */
        )) : vue$1.createCommentVNode("v-if", true)
      ]),
      default: vue$1.withCtx(() => [
        vue$1.createElementVNode("div", {
          ref: "root",
          class: "c-install-dialog__content",
          "data-step": $data.step
        }, [
          vue$1.createElementVNode("section", _hoisted_4$3, [
            $data.step === "buy" ? (vue$1.openBlock(), vue$1.createElementBlock("p", _hoisted_5$3, [
              vue$1.createVNode(_component_RichText, {
                parts: $options.texts.buy
              }, null, 8, ["parts"])
            ])) : $data.step === "license" ? (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 1 },
              [
                vue$1.createElementVNode("p", _hoisted_6$2, [
                  vue$1.createVNode(_component_RichText, {
                    parts: $options.texts.license
                  }, null, 8, ["parts"])
                ]),
                vue$1.createElementVNode("div", _hoisted_7$2, [
                  vue$1.createElementVNode("label", {
                    for: $data.inputId,
                    class: "c-mp-dialog__label"
                  }, vue$1.toDisplayString($options.labels.licenceKey), 9, _hoisted_8$2),
                  vue$1.createElementVNode(
                    "div",
                    {
                      class: vue$1.normalizeClass(["c-licence-field", { "is-checking": $data.checking }])
                    },
                    [
                      vue$1.withDirectives(vue$1.createElementVNode("input", {
                        id: $data.inputId,
                        ref: "input",
                        "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.licenceKey = $event),
                        type: "text",
                        class: vue$1.normalizeClass(["form-control c-licence-field__input", { "is-invalid": $data.keyError }]),
                        placeholder: $options.labels.placeholder,
                        autocomplete: "off",
                        spellcheck: "false",
                        autocapitalize: "characters",
                        readonly: $data.checking,
                        "aria-invalid": $data.keyError ? "true" : null,
                        "aria-describedby": $data.keyError ? `${$data.inputId}-error` : null,
                        onInput: _cache[2] || (_cache[2] = ($event) => $data.keyError = null),
                        onKeydown: _cache[3] || (_cache[3] = vue$1.withKeys(vue$1.withModifiers((...args) => $options.register && $options.register(...args), ["prevent"]), ["enter"]))
                      }, null, 42, _hoisted_9$2), [
                        [vue$1.vModelText, $data.licenceKey]
                      ]),
                      $data.checking ? (vue$1.openBlock(), vue$1.createElementBlock("i", _hoisted_10$2)) : vue$1.createCommentVNode("v-if", true)
                    ],
                    2
                    /* CLASS */
                  ),
                  $data.keyError ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
                    key: 0,
                    id: `${$data.inputId}-error`,
                    class: "invalid-feedback d-block",
                    role: "alert"
                  }, vue$1.toDisplayString($data.keyError), 9, _hoisted_11$2)) : vue$1.createCommentVNode("v-if", true)
                ])
              ],
              64
              /* STABLE_FRAGMENT */
            )) : $data.step === "confirm" ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_12$2, [
              vue$1.createElementVNode("img", {
                src: $props.module.imageUrl,
                class: "c-install-dialog__image",
                width: "48",
                height: "48",
                alt: ""
              }, null, 8, _hoisted_13$2),
              vue$1.createElementVNode("div", _hoisted_14$2, [
                vue$1.createElementVNode(
                  "b",
                  _hoisted_15$2,
                  vue$1.toDisplayString($props.module.name),
                  1
                  /* TEXT */
                ),
                $options.version ? (vue$1.openBlock(), vue$1.createElementBlock(
                  "span",
                  _hoisted_16$2,
                  vue$1.toDisplayString($options.version),
                  1
                  /* TEXT */
                )) : vue$1.createCommentVNode("v-if", true)
              ])
            ])) : $data.step === "installing" ? (vue$1.openBlock(), vue$1.createElementBlock(
              vue$1.Fragment,
              { key: 3 },
              [
                vue$1.createElementVNode("p", _hoisted_17$2, [
                  vue$1.createVNode(_component_RichText, {
                    parts: $options.texts.installing
                  }, null, 8, ["parts"])
                ]),
                vue$1.createElementVNode("div", {
                  class: "c-install-dialog__progress",
                  role: "progressbar",
                  "aria-label": $options.labels.installing,
                  "aria-valuemin": "0",
                  "aria-valuemax": "100",
                  "aria-valuenow": Math.round($data.progress * 100)
                }, [
                  vue$1.createElementVNode(
                    "span",
                    {
                      class: "c-install-dialog__progress-bar",
                      style: vue$1.normalizeStyle({ "--mp-progress": $data.progress })
                    },
                    null,
                    4
                    /* STYLE */
                  )
                ], 8, _hoisted_18$2)
              ],
              64
              /* STABLE_FRAGMENT */
            )) : $data.step === "activate" ? (vue$1.openBlock(), vue$1.createElementBlock("p", _hoisted_19$2, [
              vue$1.createVNode(_component_RichText, {
                parts: $options.texts.activate
              }, null, 8, ["parts"])
            ])) : $data.step === "configure" ? (vue$1.openBlock(), vue$1.createElementBlock("p", _hoisted_20$2, [
              vue$1.createVNode(_component_RichText, {
                parts: $options.texts.configure
              }, null, 8, ["parts"])
            ])) : $data.step === "failed" ? (vue$1.openBlock(), vue$1.createElementBlock(
              "p",
              _hoisted_21$2,
              vue$1.toDisplayString($options.labels.reloadBeforeRetry),
              1
              /* TEXT */
            )) : vue$1.createCommentVNode("v-if", true)
          ]),
          $options.showNotice ? (vue$1.openBlock(), vue$1.createElementBlock(
            "div",
            {
              key: 0,
              class: vue$1.normalizeClass(["c-mp-notice", { "c-mp-notice--warning": $props.module.isCommunity }]),
              role: "note"
            },
            [
              vue$1.createElementVNode(
                "i",
                {
                  class: vue$1.normalizeClass(["ti c-mp-notice__icon", $props.module.isCommunity ? "ti-alert-triangle" : "ti-info-circle"]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              ),
              vue$1.createElementVNode("div", _hoisted_22$2, [
                vue$1.createElementVNode(
                  "p",
                  null,
                  vue$1.toDisplayString($options.labels.thirdParty.join(" ")),
                  1
                  /* TEXT */
                ),
                $props.module.isCommunity ? (vue$1.openBlock(), vue$1.createElementBlock("p", {
                  key: 0,
                  innerHTML: $options.labels.community
                }, null, 8, _hoisted_23$2)) : vue$1.createCommentVNode("v-if", true)
              ])
            ],
            2
            /* CLASS */
          )) : vue$1.createCommentVNode("v-if", true),
          $data.error ? (vue$1.openBlock(), vue$1.createElementBlock(
            "p",
            _hoisted_24$2,
            vue$1.toDisplayString($data.error),
            1
            /* TEXT */
          )) : vue$1.createCommentVNode("v-if", true),
          $data.step === "buy" && $props.module.checkoutUrl ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
            key: 2,
            class: "btn c-install-dialog__checkout",
            href: $props.module.checkoutUrl,
            target: "_blank",
            rel: "noopener noreferrer"
          }, [
            vue$1.createTextVNode(
              vue$1.toDisplayString($options.labels.buyLicenceKey),
              1
              /* TEXT */
            ),
            _cache[14] || (_cache[14] = vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-external-link",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode(
              "span",
              _hoisted_26$2,
              vue$1.toDisplayString($options.labels.newTab),
              1
              /* TEXT */
            )
          ], 8, _hoisted_25$2)) : vue$1.createCommentVNode("v-if", true)
        ], 8, _hoisted_3$3)
      ]),
      _: 1
      /* STABLE */
    }, 8, ["backdrop-close", "keyboard", "onUpdate:show"]);
  }
  const InstallDialog = /* @__PURE__ */ _export_sfc(_sfc_main$3, [["render", _sfc_render$3]]);
  const COMBINING = /[̀-ͯ]/g;
  const fold = (value) => value.normalize("NFD").replace(COMBINING, "").toLowerCase();
  const foldWithMap = (text) => {
    let folded = "";
    const source = [];
    for (let i = 0; i < text.length; i++) {
      const part = fold(text[i]);
      folded += part;
      for (let j = 0; j < part.length; j++) {
        source.push(i);
      }
    }
    return { folded, source };
  };
  const highlightParts = (text, query) => {
    const value = String(text ?? "");
    const needle = fold(String(query ?? "").trim());
    if (needle === "" || value === "") {
      return value === "" ? [] : [{ text: value, mark: false }];
    }
    const { folded, source } = foldWithMap(value);
    const parts = [];
    let cursor = 0;
    let from = folded.indexOf(needle);
    while (from !== -1) {
      const start = source[from];
      let end = source[from + needle.length - 1] + 1;
      while (end < value.length && fold(value[end]) === "") {
        end++;
      }
      if (start >= cursor) {
        if (start > cursor) {
          parts.push({ text: value.slice(cursor, start), mark: false });
        }
        parts.push({ text: value.slice(start, end), mark: true });
        cursor = end;
      }
      from = folded.indexOf(needle, from + needle.length);
    }
    if (cursor < value.length) {
      parts.push({ text: value.slice(cursor), mark: false });
    }
    return parts;
  };
  const FILTER_TAGS = ["professional", "official", "community", "partner"];
  const _sfc_main$2 = {
    name: "ModuleCard",
    components: { RichText },
    props: {
      module: { type: Object, required: true },
      error: { type: String, default: null },
      updateState: { type: String, default: null },
      locked: { type: Boolean, default: false },
      professionalEditionUrl: { type: String, required: true },
      query: { type: String, default: "" }
    },
    emits: ["install", "buy", "update", "filter-type"],
    computed: {
      titleParts() {
        return highlightParts(this.module.name, this.query);
      },
      descriptionParts() {
        return highlightParts(this.module.description, this.query);
      },
      isInstalled() {
        return this.module.installedVersion !== null && this.module.installedVersion !== void 0;
      },
      // Stays the update button through "Updated!" and the fade-out, until the browser
      // replaced the module with its updated record.
      showUpdateAction() {
        return this.isInstalled && this.module.updateAvailable || this.updateState === "success" || this.updateState === "removing";
      },
      showUpdatePill() {
        return this.showUpdateAction && Boolean(this.module.latestCompatibleVersion);
      },
      cardClasses() {
        return {
          "is-updating": ["running", "success", "removing"].includes(this.updateState),
          "is-removing": this.updateState === "removing",
          "is-entering": this.updateState === "done"
        };
      },
      updateBusy() {
        return ["pending", "running", "success", "removing"].includes(this.updateState) || this.locked;
      },
      updateButtonClass() {
        return ["running", "success", "removing"].includes(this.updateState) ? "btn-warning is-updating" : "btn-accent";
      },
      updateLabel() {
        switch (this.updateState) {
          case "pending":
            return this.labels.queued;
          case "running":
            return this.labels.updating;
          case "success":
          case "removing":
            return this.labels.updated;
          default:
            return this.labels.update;
        }
      },
      filterTag() {
        return FILTER_TAGS.includes(this.module.badge) ? this.module.badge : null;
      },
      badgeText() {
        return this.module.badge === "professional" ? vue.i18n.t("MarketplaceModule.base", "PRO") : badgeLabel(this.module.badge);
      },
      badgeTitle() {
        if (this.module.badge === "community") {
          return `${this.labels.thirdParty} (${vue.i18n.t("MarketplaceModule.base", "Unverified Community")})`;
        }
        if (this.module.badge === "partner") {
          return this.labels.thirdParty;
        }
        return this.filterTag ? this.badgeFilterLabel : null;
      },
      badgeFilterLabel() {
        return vue.i18n.t("MarketplaceModule.base", "Filter by {type}", { type: badgeLabel(this.module.badge) });
      },
      versionText() {
        if (this.isInstalled) {
          return this.module.installedVersion;
        }
        return this.module.latestCompatibleVersion || this.module.latestVersion || "";
      },
      buyLabel() {
        return buyLabel(this.module.price);
      },
      secondaryLink() {
        if (!this.module.marketplaceUrl) {
          return null;
        }
        return this.showUpdateAction ? { href: `${this.module.marketplaceUrl}/changelog`, icon: "ti-file-text", label: this.labels.changelog } : { href: this.module.marketplaceUrl, icon: "ti-info-circle", label: this.labels.information };
      },
      labels() {
        return {
          featured: vue.i18n.t("MarketplaceModule.base", "Featured"),
          featuredFilter: vue.i18n.t("MarketplaceModule.base", "Featured: filter by Featured"),
          install: vue.i18n.t("MarketplaceModule.base", "Install"),
          learnMore: vue.i18n.t("MarketplaceModule.base", "Learn more"),
          installed: vue.i18n.t("MarketplaceModule.base", "Installed"),
          notCompatible: vue.i18n.t("MarketplaceModule.base", "Not compatible"),
          update: vue.i18n.t("MarketplaceModule.base", "Update"),
          updating: vue.i18n.t("MarketplaceModule.base", "Updating"),
          updated: vue.i18n.t("MarketplaceModule.base", "Updated!"),
          queued: vue.i18n.t("MarketplaceModule.base", "Queued"),
          updateFailed: vue.i18n.t("MarketplaceModule.base", "Update failed"),
          updateTo: vue.i18n.t("MarketplaceModule.base", "Update to version {version} available", { version: this.module.latestCompatibleVersion }),
          changelog: vue.i18n.t("MarketplaceModule.base", "Module changelog"),
          information: vue.i18n.t("MarketplaceModule.base", "Module information"),
          thirdParty: vue.i18n.t("MarketplaceModule.base", "This Module was developed by a third-party.")
        };
      }
    }
  };
  const _hoisted_1$2 = ["aria-busy"];
  const _hoisted_2$2 = { class: "c-module-card__cover" };
  const _hoisted_3$2 = ["href"];
  const _hoisted_4$2 = ["src"];
  const _hoisted_5$2 = ["src"];
  const _hoisted_6$1 = { class: "c-module-card__badges" };
  const _hoisted_7$1 = ["aria-label", "title"];
  const _hoisted_8$1 = ["title"];
  const _hoisted_9$1 = ["title", "aria-label"];
  const _hoisted_10$1 = ["title"];
  const _hoisted_11$1 = { class: "c-module-card__header" };
  const _hoisted_12$1 = ["href"];
  const _hoisted_13$1 = {
    key: 1,
    class: "c-module-card__title"
  };
  const _hoisted_14$1 = { class: "c-module-card__version" };
  const _hoisted_15$1 = { class: "c-module-card__body" };
  const _hoisted_16$1 = { class: "c-module-card__text" };
  const _hoisted_17$1 = {
    key: 0,
    class: "c-module-card__message text-danger",
    role: "alert"
  };
  const _hoisted_18$1 = {
    key: 1,
    class: "c-module-card__message text-danger",
    role: "alert"
  };
  const _hoisted_19$1 = { class: "c-module-card__footer" };
  const _hoisted_20$1 = ["disabled"];
  const _hoisted_21$1 = {
    key: 0,
    class: "ti ti-refresh c-module-card__spin",
    "aria-hidden": "true"
  };
  const _hoisted_22$1 = {
    key: 1,
    type: "button",
    class: "btn btn-secondary c-module-card__action",
    disabled: ""
  };
  const _hoisted_23$1 = ["disabled"];
  const _hoisted_24$1 = ["disabled"];
  const _hoisted_25$1 = ["href"];
  const _hoisted_26$1 = {
    key: 5,
    type: "button",
    class: "btn btn-secondary c-module-card__action",
    disabled: ""
  };
  const _hoisted_27$1 = ["href", "aria-label", "title"];
  function _sfc_render$2(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_RichText = vue$1.resolveComponent("RichText");
    return vue$1.openBlock(), vue$1.createElementBlock("article", {
      class: vue$1.normalizeClass(["c-module-card", $options.cardClasses]),
      "aria-busy": $props.updateState === "running" ? "true" : null
    }, [
      vue$1.createElementVNode("div", _hoisted_2$2, [
        $props.module.marketplaceUrl ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
          key: 0,
          href: $props.module.marketplaceUrl,
          target: "_blank",
          rel: "noopener",
          "aria-hidden": "true",
          tabindex: "-1"
        }, [
          vue$1.createElementVNode("img", {
            src: $props.module.imageUrl,
            class: "c-module-card__image",
            width: "80",
            height: "80",
            alt: ""
          }, null, 8, _hoisted_4$2)
        ], 8, _hoisted_3$2)) : (vue$1.openBlock(), vue$1.createElementBlock("img", {
          key: 1,
          src: $props.module.imageUrl,
          class: "c-module-card__image",
          width: "80",
          height: "80",
          alt: ""
        }, null, 8, _hoisted_5$2)),
        vue$1.createElementVNode("div", _hoisted_6$1, [
          $props.module.featured ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
            key: 0,
            type: "button",
            class: "c-module-card__badge c-module-card__badge--filter c-module-card__badge--featured c-module-card__badge--star",
            "aria-label": $options.labels.featuredFilter,
            title: $options.labels.featured,
            onClick: _cache[0] || (_cache[0] = ($event) => _ctx.$emit("filter-type", "featured"))
          }, [..._cache[5] || (_cache[5] = [
            vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-star-filled",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )
          ])], 8, _hoisted_7$1)) : vue$1.createCommentVNode("v-if", true),
          $options.showUpdatePill ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
            key: 1,
            class: "c-module-card__badge c-module-card__badge--update",
            title: $options.labels.updateTo
          }, [
            _cache[6] || (_cache[6] = vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-arrow-up",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue$1.createElementVNode(
              "span",
              null,
              vue$1.toDisplayString($props.module.latestCompatibleVersion),
              1
              /* TEXT */
            )
          ], 8, _hoisted_8$1)) : $options.filterTag ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
            key: 2,
            type: "button",
            class: vue$1.normalizeClass(["c-module-card__badge c-module-card__badge--filter", `c-module-card__badge--${$props.module.badge}`]),
            title: $options.badgeTitle,
            "aria-label": $options.badgeFilterLabel,
            onClick: _cache[1] || (_cache[1] = ($event) => _ctx.$emit("filter-type", $options.filterTag))
          }, vue$1.toDisplayString($options.badgeText), 11, _hoisted_9$1)) : $options.badgeText ? (vue$1.openBlock(), vue$1.createElementBlock("span", {
            key: 3,
            class: vue$1.normalizeClass(["c-module-card__badge", `c-module-card__badge--${$props.module.badge}`]),
            title: $options.badgeTitle
          }, vue$1.toDisplayString($options.badgeText), 11, _hoisted_10$1)) : vue$1.createCommentVNode("v-if", true)
        ])
      ]),
      vue$1.createElementVNode("div", _hoisted_11$1, [
        $props.module.marketplaceUrl ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
          key: 0,
          class: "c-module-card__title",
          href: $props.module.marketplaceUrl,
          target: "_blank",
          rel: "noopener"
        }, [
          vue$1.createVNode(_component_RichText, {
            parts: $options.titleParts,
            flag: "mark",
            tag: "mark",
            "mark-class": "c-module-card__mark"
          }, null, 8, ["parts"])
        ], 8, _hoisted_12$1)) : (vue$1.openBlock(), vue$1.createElementBlock("span", _hoisted_13$1, [
          vue$1.createVNode(_component_RichText, {
            parts: $options.titleParts,
            flag: "mark",
            tag: "mark",
            "mark-class": "c-module-card__mark"
          }, null, 8, ["parts"])
        ])),
        vue$1.createElementVNode(
          "p",
          _hoisted_14$1,
          vue$1.toDisplayString($options.versionText),
          1
          /* TEXT */
        )
      ]),
      vue$1.createElementVNode("div", _hoisted_15$1, [
        vue$1.createElementVNode("p", _hoisted_16$1, [
          vue$1.createVNode(_component_RichText, {
            parts: $options.descriptionParts,
            flag: "mark",
            tag: "mark",
            "mark-class": "c-module-card__mark"
          }, null, 8, ["parts"])
        ]),
        $props.error ? (vue$1.openBlock(), vue$1.createElementBlock(
          "p",
          _hoisted_17$1,
          vue$1.toDisplayString($props.error),
          1
          /* TEXT */
        )) : $props.updateState === "failed" ? (vue$1.openBlock(), vue$1.createElementBlock(
          "p",
          _hoisted_18$1,
          vue$1.toDisplayString($options.labels.updateFailed),
          1
          /* TEXT */
        )) : vue$1.createCommentVNode("v-if", true)
      ]),
      vue$1.createElementVNode("div", _hoisted_19$1, [
        $options.showUpdateAction ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
          key: 0,
          type: "button",
          class: vue$1.normalizeClass(["btn c-module-card__action", $options.updateButtonClass]),
          disabled: $options.updateBusy,
          onClick: _cache[2] || (_cache[2] = ($event) => _ctx.$emit("update"))
        }, [
          $props.updateState === "running" ? (vue$1.openBlock(), vue$1.createElementBlock("i", _hoisted_21$1)) : vue$1.createCommentVNode("v-if", true),
          vue$1.createElementVNode(
            "span",
            null,
            vue$1.toDisplayString($options.updateLabel),
            1
            /* TEXT */
          )
        ], 10, _hoisted_20$1)) : $options.isInstalled ? (vue$1.openBlock(), vue$1.createElementBlock(
          "button",
          _hoisted_22$1,
          vue$1.toDisplayString($options.labels.installed),
          1
          /* TEXT */
        )) : $props.module.availability === "install" ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
          key: 2,
          type: "button",
          class: "btn btn-primary c-module-card__action",
          disabled: $props.locked,
          onClick: _cache[3] || (_cache[3] = ($event) => _ctx.$emit("install"))
        }, vue$1.toDisplayString($options.labels.install), 9, _hoisted_23$1)) : $props.module.availability === "buy" ? (vue$1.openBlock(), vue$1.createElementBlock("button", {
          key: 3,
          type: "button",
          class: "btn btn-primary c-module-card__action",
          disabled: $props.locked,
          onClick: _cache[4] || (_cache[4] = ($event) => _ctx.$emit("buy"))
        }, vue$1.toDisplayString($options.buyLabel), 9, _hoisted_24$1)) : $props.module.availability === "professionalEdition" ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
          key: 4,
          href: $props.professionalEditionUrl,
          class: "btn btn-primary c-module-card__action",
          target: "_blank",
          rel: "noopener"
        }, vue$1.toDisplayString($options.labels.learnMore), 9, _hoisted_25$1)) : (vue$1.openBlock(), vue$1.createElementBlock(
          "button",
          _hoisted_26$1,
          vue$1.toDisplayString($options.labels.notCompatible),
          1
          /* TEXT */
        )),
        $options.secondaryLink ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
          key: 6,
          href: $options.secondaryLink.href,
          class: "btn c-icon-button c-icon-button--ghost c-module-card__secondary",
          target: "_blank",
          rel: "noopener",
          "aria-label": $options.secondaryLink.label,
          title: $options.secondaryLink.label
        }, [
          vue$1.createElementVNode(
            "i",
            {
              class: vue$1.normalizeClass(["ti", $options.secondaryLink.icon]),
              "aria-hidden": "true"
            },
            null,
            2
            /* CLASS */
          )
        ], 8, _hoisted_27$1)) : vue$1.createCommentVNode("v-if", true)
      ])
    ], 10, _hoisted_1$2);
  }
  const ModuleCard = /* @__PURE__ */ _export_sfc(_sfc_main$2, [["render", _sfc_render$2]]);
  const ADDED_MS = 1600;
  const TABS = ["licenses", "settings"];
  let uidSeq = 0;
  const _sfc_main$1 = {
    name: "SettingsDialog",
    props: {
      settings: { type: Object, required: true },
      installationId: { type: String, default: "" },
      moduleAdministrationUrl: { type: String, default: null },
      initialTab: {
        type: String,
        default: "licenses",
        validator: (value) => TABS.includes(value)
      },
      // Another action runs: a licence row cannot start an installation.
      installLocked: { type: Boolean, default: false }
    },
    emits: ["registered", "settings-changed", "install", "close"],
    data() {
      return {
        uid: `marketplace-settings-${++uidSeq}`,
        tab: this.initialTab,
        licenceKey: "",
        keyError: null,
        registering: false,
        added: false,
        loading: true,
        licences: [],
        enteringId: null,
        beta: this.settings.includeBetaUpdates === true,
        community: this.settings.includeCommunityModules === true,
        acknowledging: false,
        acknowledged: false,
        // The setting being saved (`includeBetaUpdates` / `includeCommunityModules`), or null.
        saving: null,
        settingsError: null
      };
    },
    computed: {
      tabs() {
        return [
          { key: "licenses", icon: "ti-key", label: this.labels.licenses },
          { key: "settings", icon: "ti-settings", label: this.labels.settings }
        ];
      },
      labels() {
        return {
          title: vue.i18n.t("MarketplaceModule.base", "Marketplace Settings"),
          licenses: vue.i18n.t("MarketplaceModule.base", "Licenses"),
          settings: vue.i18n.t("base", "Settings"),
          licenceKey: vue.i18n.t("MarketplaceModule.base", "License Key"),
          placeholder: "XXXX-XXXX-XXXX-XXXX",
          addLicenceKey: vue.i18n.t("MarketplaceModule.base", "Add License Key"),
          none: vue.i18n.t("MarketplaceModule.base", "No purchased modules found!"),
          install: vue.i18n.t("MarketplaceModule.base", "Install"),
          installed: vue.i18n.t("MarketplaceModule.base", "Installed"),
          information: vue.i18n.t("MarketplaceModule.base", "Module information"),
          installationId: vue.i18n.t("MarketplaceModule.base", "Installation ID: {installationId}", { installationId: this.installationId }),
          beta: vue.i18n.t("MarketplaceModule.base", "Beta Modules"),
          betaHint: vue.i18n.t("MarketplaceModule.base", "Allow modules in Beta to be installed."),
          community: vue.i18n.t("MarketplaceModule.base", "Unverified Modules"),
          communityHint: vue.i18n.t("MarketplaceModule.base", "Show modules contributed by the community that are not tested or maintained by the HumHub team."),
          warning: communityWarning(),
          acknowledge: communityAcknowledge(),
          confirm: vue.i18n.t("base", "Confirm"),
          moduleAdministration: vue.i18n.t("MarketplaceModule.base", "Module Administration"),
          loading: vue.i18n.t("base", "Loading..."),
          close: vue.i18n.t("base", "Close")
        };
      }
    },
    created() {
      this.loadSeq = 0;
      this.knownIds = null;
      this.load();
    },
    beforeUnmount() {
      clearTimeout(this.addedTimer);
    },
    methods: {
      onShow(show) {
        if (!show) {
          this.$emit("close");
        }
      },
      onTabKeydown(event) {
        const index = TABS.indexOf(this.tab);
        const next = {
          ArrowRight: (index + 1) % TABS.length,
          ArrowLeft: (index - 1 + TABS.length) % TABS.length,
          Home: 0,
          End: TABS.length - 1
        }[event.key];
        if (next === void 0) {
          return;
        }
        event.preventDefault();
        this.tab = TABS[next];
        this.$nextTick(() => {
          const [button] = [].concat(this.$refs[`tab-${this.tab}`] || []);
          if (button) {
            button.focus();
          }
        });
      },
      isInstalled(module) {
        return module.installedVersion !== null && module.installedVersion !== void 0;
      },
      licenceText(module) {
        return vue.i18n.t("MarketplaceModule.base", "License: {licenceKey}", { licenceKey: module.licenceKey || "" });
      },
      installLabel(module) {
        return vue.i18n.t("MarketplaceModule.base", "Install {moduleName}", { moduleName: module.name });
      },
      // Only the latest load is applied, however the requests answer. The ids of a completed
      // load tell which licence a registered key added; after a failed one nothing is known.
      load() {
        const seq = ++this.loadSeq;
        this.loading = true;
        return fetchModules({ tag: "purchased" }).then((modules) => {
          if (seq === this.loadSeq) {
            this.licences = modules;
            this.knownIds = new Set(modules.map((module) => module.id));
          }
        }, (response) => {
          vue.log.error(response);
          if (seq === this.loadSeq) {
            this.licences = [];
            this.knownIds = null;
          }
        }).finally(() => {
          if (seq === this.loadSeq) {
            this.loading = false;
          }
        });
      },
      onKeyInput() {
        this.keyError = null;
        this.added = false;
      },
      async register() {
        const key = this.licenceKey.trim();
        if (key === "" || this.registering || this.loading) {
          return;
        }
        this.registering = true;
        this.keyError = null;
        const known = this.knownIds;
        try {
          await registerLicenceKey(key);
        } catch (response) {
          this.registering = false;
          this.keyError = errorMessage(response, vue.i18n.t("MarketplaceModule.base", "Invalid module license key!"));
          vue.log.error(response);
          return;
        }
        this.$emit("registered");
        await this.load();
        this.registering = false;
        this.licenceKey = "";
        const added = known ? this.licences.find((module) => !known.has(module.id)) : void 0;
        if (added) {
          this.licences = [added, ...this.licences.filter((module) => module !== added)];
          this.enteringId = added.id;
        }
        vue.status("success", added ? vue.i18n.t("MarketplaceModule.base", "License added for {moduleName}.", { moduleName: added.name }) : vue.i18n.t("MarketplaceModule.base", "License added."));
        this.added = true;
        clearTimeout(this.addedTimer);
        this.addedTimer = setTimeout(() => {
          this.added = false;
        }, ADDED_MS);
        this.$nextTick(() => {
          if (this.$refs.input) {
            this.$refs.input.focus();
          }
        });
      },
      toggleBeta(checked) {
        this.save("includeBetaUpdates", checked);
      },
      toggleCommunity(checked) {
        this.settingsError = null;
        if (!checked) {
          if (this.acknowledging) {
            this.acknowledging = false;
            this.acknowledged = false;
            return;
          }
          this.save("includeCommunityModules", false);
          return;
        }
        this.acknowledging = true;
        this.acknowledged = false;
        this.$nextTick(() => {
          if (this.$refs.acknowledged) {
            this.$refs.acknowledged.focus();
          }
        });
      },
      confirmCommunity() {
        if (this.acknowledged) {
          this.save("includeCommunityModules", true);
        }
      },
      save(key, value) {
        if (this.saving !== null) {
          return;
        }
        const local = key === "includeBetaUpdates" ? "beta" : "community";
        const before = this[local];
        this[local] = value;
        this.saving = key;
        this.settingsError = null;
        saveSettings({ [key]: value ? 1 : 0 }).then((settings) => {
          this.saving = null;
          this.beta = settings.includeBetaUpdates;
          this.community = settings.includeCommunityModules;
          if (key === "includeCommunityModules") {
            this.acknowledging = false;
            this.acknowledged = false;
          }
          this.$emit("settings-changed", settings);
        }).catch((response) => {
          this.saving = null;
          this[local] = before;
          this.settingsError = errorMessage(response, vue.i18n.t("MarketplaceModule.base", "The settings could not be saved."));
          vue.log.error(response);
        });
      }
    }
  };
  const _hoisted_1$1 = ["aria-label"];
  const _hoisted_2$1 = ["id", "aria-selected", "aria-controls", "tabindex", "onClick"];
  const _hoisted_3$1 = ["id", "aria-labelledby"];
  const _hoisted_4$1 = { class: "c-mp-settings__key" };
  const _hoisted_5$1 = ["placeholder", "aria-label", "disabled", "aria-invalid", "aria-describedby"];
  const _hoisted_6 = ["aria-label", "title", "disabled"];
  const _hoisted_7 = ["id"];
  const _hoisted_8 = {
    key: 1,
    class: "c-mp-settings__loading"
  };
  const _hoisted_9 = ["aria-label"];
  const _hoisted_10 = {
    key: 2,
    class: "c-mp-settings__empty"
  };
  const _hoisted_11 = {
    key: 3,
    class: "c-licence-list"
  };
  const _hoisted_12 = { class: "c-licence-list__clip" };
  const _hoisted_13 = { class: "c-licence-list__row" };
  const _hoisted_14 = ["src"];
  const _hoisted_15 = { class: "c-licence-list__text" };
  const _hoisted_16 = { class: "c-licence-list__name" };
  const _hoisted_17 = { class: "c-licence-list__key" };
  const _hoisted_18 = {
    key: 0,
    type: "button",
    class: "btn btn-secondary c-licence-list__action",
    disabled: ""
  };
  const _hoisted_19 = ["aria-label", "disabled", "onClick"];
  const _hoisted_20 = ["href", "aria-label", "title"];
  const _hoisted_21 = {
    key: 4,
    class: "c-mp-settings__installation"
  };
  const _hoisted_22 = ["id", "aria-labelledby"];
  const _hoisted_23 = { class: "c-setting-row" };
  const _hoisted_24 = { class: "form-check" };
  const _hoisted_25 = ["id", "checked", "disabled", "aria-describedby"];
  const _hoisted_26 = ["for"];
  const _hoisted_27 = ["id"];
  const _hoisted_28 = { class: "form-check" };
  const _hoisted_29 = ["id", "checked", "disabled", "aria-describedby"];
  const _hoisted_30 = ["for"];
  const _hoisted_31 = ["id"];
  const _hoisted_32 = { class: "c-setting-row__more" };
  const _hoisted_33 = { class: "c-setting-row__more-inner" };
  const _hoisted_34 = {
    key: 0,
    class: "c-mp-notice c-mp-notice--warning"
  };
  const _hoisted_35 = { class: "c-mp-notice__text" };
  const _hoisted_36 = ["innerHTML"];
  const _hoisted_37 = { class: "form-check" };
  const _hoisted_38 = ["id"];
  const _hoisted_39 = ["for"];
  const _hoisted_40 = ["disabled"];
  const _hoisted_41 = {
    key: 0,
    class: "ti ti-loader c-mp-spin",
    "aria-hidden": "true"
  };
  const _hoisted_42 = {
    key: 0,
    class: "c-mp-dialog__error",
    role: "alert"
  };
  const _hoisted_43 = ["href"];
  function _sfc_render$1(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_UiModal = vue$1.resolveComponent("UiModal");
    return vue$1.openBlock(), vue$1.createBlock(_component_UiModal, {
      show: true,
      "dialog-class": "c-mp-dialog c-mp-settings",
      title: $options.labels.title,
      "onUpdate:show": $options.onShow
    }, {
      footer: vue$1.withCtx(() => [
        vue$1.createElementVNode(
          "button",
          {
            type: "button",
            class: "btn btn-secondary",
            onClick: _cache[9] || (_cache[9] = ($event) => _ctx.$emit("close"))
          },
          vue$1.toDisplayString($options.labels.close),
          1
          /* TEXT */
        )
      ]),
      default: vue$1.withCtx(() => [
        vue$1.createElementVNode("div", {
          class: "c-mp-settings__tabs",
          role: "tablist",
          "aria-label": $options.labels.title
        }, [
          (vue$1.openBlock(true), vue$1.createElementBlock(
            vue$1.Fragment,
            null,
            vue$1.renderList($options.tabs, (item) => {
              return vue$1.openBlock(), vue$1.createElementBlock("button", {
                id: `${$data.uid}-tab-${item.key}`,
                key: item.key,
                ref_for: true,
                ref: `tab-${item.key}`,
                type: "button",
                role: "tab",
                class: vue$1.normalizeClass(["c-mp-settings__tab", { "is-active": $data.tab === item.key }]),
                "aria-selected": $data.tab === item.key ? "true" : "false",
                "aria-controls": `${$data.uid}-panel-${item.key}`,
                tabindex: $data.tab === item.key ? null : -1,
                onClick: ($event) => $data.tab = item.key,
                onKeydown: _cache[0] || (_cache[0] = (...args) => $options.onTabKeydown && $options.onTabKeydown(...args))
              }, [
                vue$1.createElementVNode(
                  "i",
                  {
                    class: vue$1.normalizeClass(["ti", item.icon]),
                    "aria-hidden": "true"
                  },
                  null,
                  2
                  /* CLASS */
                ),
                vue$1.createTextVNode(
                  vue$1.toDisplayString(item.label),
                  1
                  /* TEXT */
                )
              ], 42, _hoisted_2$1);
            }),
            128
            /* KEYED_FRAGMENT */
          ))
        ], 8, _hoisted_1$1),
        vue$1.withDirectives(vue$1.createElementVNode("section", {
          id: `${$data.uid}-panel-licenses`,
          class: "c-mp-settings__panel",
          role: "tabpanel",
          "aria-labelledby": `${$data.uid}-tab-licenses`
        }, [
          vue$1.createElementVNode("div", _hoisted_4$1, [
            vue$1.createElementVNode(
              "div",
              {
                class: vue$1.normalizeClass(["c-licence-field", { "is-valid": $data.added }])
              },
              [
                vue$1.withDirectives(vue$1.createElementVNode("input", {
                  ref: "input",
                  "onUpdate:modelValue": _cache[1] || (_cache[1] = ($event) => $data.licenceKey = $event),
                  type: "text",
                  class: vue$1.normalizeClass(["form-control c-licence-field__input", { "is-invalid": $data.keyError, "is-valid": $data.added }]),
                  placeholder: $options.labels.placeholder,
                  "aria-label": $options.labels.licenceKey,
                  autocomplete: "off",
                  spellcheck: "false",
                  autocapitalize: "characters",
                  disabled: $data.registering || $data.loading,
                  "aria-invalid": $data.keyError ? "true" : null,
                  "aria-describedby": $data.keyError ? `${$data.uid}-key-error` : null,
                  onInput: _cache[2] || (_cache[2] = (...args) => $options.onKeyInput && $options.onKeyInput(...args)),
                  onKeydown: _cache[3] || (_cache[3] = vue$1.withKeys(vue$1.withModifiers((...args) => $options.register && $options.register(...args), ["prevent"]), ["enter"]))
                }, null, 42, _hoisted_5$1), [
                  [vue$1.vModelText, $data.licenceKey]
                ])
              ],
              2
              /* CLASS */
            ),
            vue$1.createElementVNode("button", {
              type: "button",
              class: "btn btn-primary c-icon-button c-mp-settings__add",
              "aria-label": $options.labels.addLicenceKey,
              title: $options.labels.addLicenceKey,
              disabled: $data.registering || $data.loading || $data.licenceKey.trim() === "",
              onClick: _cache[4] || (_cache[4] = (...args) => $options.register && $options.register(...args))
            }, [
              vue$1.createElementVNode(
                "i",
                {
                  class: vue$1.normalizeClass(["ti", $data.registering ? "ti-loader c-mp-spin" : $data.added ? "ti-check" : "ti-plus"]),
                  "aria-hidden": "true"
                },
                null,
                2
                /* CLASS */
              )
            ], 8, _hoisted_6)
          ]),
          $data.keyError ? (vue$1.openBlock(), vue$1.createElementBlock("div", {
            key: 0,
            id: `${$data.uid}-key-error`,
            class: "invalid-feedback d-block",
            role: "alert"
          }, vue$1.toDisplayString($data.keyError), 9, _hoisted_7)) : vue$1.createCommentVNode("v-if", true),
          $data.loading ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_8, [
            vue$1.createElementVNode("span", {
              class: "spinner-border spinner-border-sm",
              role: "status",
              "aria-label": $options.labels.loading
            }, null, 8, _hoisted_9)
          ])) : !$data.licences.length ? (vue$1.openBlock(), vue$1.createElementBlock(
            "p",
            _hoisted_10,
            vue$1.toDisplayString($options.labels.none),
            1
            /* TEXT */
          )) : (vue$1.openBlock(), vue$1.createElementBlock("ul", _hoisted_11, [
            (vue$1.openBlock(true), vue$1.createElementBlock(
              vue$1.Fragment,
              null,
              vue$1.renderList($data.licences, (module) => {
                return vue$1.openBlock(), vue$1.createElementBlock(
                  "li",
                  {
                    key: module.id,
                    class: vue$1.normalizeClass(["c-licence-list__item", { "is-entering": module.id === $data.enteringId }])
                  },
                  [
                    vue$1.createElementVNode("div", _hoisted_12, [
                      vue$1.createElementVNode("div", _hoisted_13, [
                        vue$1.createElementVNode("img", {
                          src: module.imageUrl,
                          class: "c-licence-list__image",
                          width: "40",
                          height: "40",
                          alt: ""
                        }, null, 8, _hoisted_14),
                        vue$1.createElementVNode("div", _hoisted_15, [
                          vue$1.createElementVNode(
                            "p",
                            _hoisted_16,
                            vue$1.toDisplayString(module.name),
                            1
                            /* TEXT */
                          ),
                          vue$1.createElementVNode(
                            "p",
                            _hoisted_17,
                            vue$1.toDisplayString($options.licenceText(module)),
                            1
                            /* TEXT */
                          )
                        ]),
                        $options.isInstalled(module) ? (vue$1.openBlock(), vue$1.createElementBlock(
                          "button",
                          _hoisted_18,
                          vue$1.toDisplayString($options.labels.installed),
                          1
                          /* TEXT */
                        )) : (vue$1.openBlock(), vue$1.createElementBlock("button", {
                          key: 1,
                          type: "button",
                          class: "btn btn-primary c-licence-list__action",
                          "aria-label": $options.installLabel(module),
                          disabled: $props.installLocked,
                          onClick: ($event) => _ctx.$emit("install", module)
                        }, vue$1.toDisplayString($options.labels.install), 9, _hoisted_19)),
                        module.marketplaceUrl ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
                          key: 2,
                          href: module.marketplaceUrl,
                          class: "btn c-icon-button c-icon-button--ghost",
                          target: "_blank",
                          rel: "noopener",
                          "aria-label": $options.labels.information,
                          title: $options.labels.information
                        }, [..._cache[10] || (_cache[10] = [
                          vue$1.createElementVNode(
                            "i",
                            {
                              class: "ti ti-info-circle",
                              "aria-hidden": "true"
                            },
                            null,
                            -1
                            /* CACHED */
                          )
                        ])], 8, _hoisted_20)) : vue$1.createCommentVNode("v-if", true)
                      ])
                    ])
                  ],
                  2
                  /* CLASS */
                );
              }),
              128
              /* KEYED_FRAGMENT */
            ))
          ])),
          $props.installationId ? (vue$1.openBlock(), vue$1.createElementBlock(
            "p",
            _hoisted_21,
            vue$1.toDisplayString($options.labels.installationId),
            1
            /* TEXT */
          )) : vue$1.createCommentVNode("v-if", true)
        ], 8, _hoisted_3$1), [
          [vue$1.vShow, $data.tab === "licenses"]
        ]),
        vue$1.withDirectives(vue$1.createElementVNode("section", {
          id: `${$data.uid}-panel-settings`,
          class: "c-mp-settings__panel c-mp-settings__options",
          role: "tabpanel",
          "aria-labelledby": `${$data.uid}-tab-settings`
        }, [
          vue$1.createElementVNode("div", _hoisted_23, [
            vue$1.createElementVNode("div", _hoisted_24, [
              vue$1.createElementVNode("input", {
                id: `${$data.uid}-beta`,
                class: "form-check-input",
                type: "checkbox",
                "data-setting": "includeBetaUpdates",
                checked: $data.beta,
                disabled: $data.saving !== null,
                "aria-describedby": `${$data.uid}-beta-hint`,
                onChange: _cache[5] || (_cache[5] = ($event) => $options.toggleBeta($event.target.checked))
              }, null, 40, _hoisted_25),
              vue$1.createElementVNode("label", {
                class: "form-check-label c-setting-row__title",
                for: `${$data.uid}-beta`
              }, vue$1.toDisplayString($options.labels.beta), 9, _hoisted_26),
              vue$1.createElementVNode("div", {
                id: `${$data.uid}-beta-hint`,
                class: "form-text c-setting-row__hint"
              }, vue$1.toDisplayString($options.labels.betaHint), 9, _hoisted_27)
            ])
          ]),
          vue$1.createElementVNode(
            "div",
            {
              class: vue$1.normalizeClass(["c-setting-row", { "is-expanded": $data.acknowledging }])
            },
            [
              vue$1.createElementVNode("div", _hoisted_28, [
                vue$1.createElementVNode("input", {
                  id: `${$data.uid}-community`,
                  class: "form-check-input",
                  type: "checkbox",
                  "data-setting": "includeCommunityModules",
                  checked: $data.community || $data.acknowledging,
                  disabled: $data.saving !== null,
                  "aria-describedby": `${$data.uid}-community-hint`,
                  onChange: _cache[6] || (_cache[6] = ($event) => $options.toggleCommunity($event.target.checked))
                }, null, 40, _hoisted_29),
                vue$1.createElementVNode("label", {
                  class: "form-check-label c-setting-row__title",
                  for: `${$data.uid}-community`
                }, vue$1.toDisplayString($options.labels.community), 9, _hoisted_30),
                vue$1.createElementVNode("div", {
                  id: `${$data.uid}-community-hint`,
                  class: "form-text c-setting-row__hint"
                }, vue$1.toDisplayString($options.labels.communityHint), 9, _hoisted_31)
              ]),
              vue$1.createElementVNode("div", _hoisted_32, [
                vue$1.createElementVNode("div", _hoisted_33, [
                  $data.acknowledging ? (vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_34, [
                    _cache[11] || (_cache[11] = vue$1.createElementVNode(
                      "i",
                      {
                        class: "ti ti-alert-triangle c-mp-notice__icon",
                        "aria-hidden": "true"
                      },
                      null,
                      -1
                      /* CACHED */
                    )),
                    vue$1.createElementVNode("div", _hoisted_35, [
                      vue$1.createElementVNode("div", {
                        innerHTML: $options.labels.warning
                      }, null, 8, _hoisted_36),
                      vue$1.createElementVNode("div", _hoisted_37, [
                        vue$1.withDirectives(vue$1.createElementVNode("input", {
                          id: `${$data.uid}-acknowledged`,
                          ref: "acknowledged",
                          "onUpdate:modelValue": _cache[7] || (_cache[7] = ($event) => $data.acknowledged = $event),
                          class: "form-check-input",
                          type: "checkbox"
                        }, null, 8, _hoisted_38), [
                          [vue$1.vModelCheckbox, $data.acknowledged]
                        ]),
                        vue$1.createElementVNode("label", {
                          class: "form-check-label",
                          for: `${$data.uid}-acknowledged`
                        }, vue$1.toDisplayString($options.labels.acknowledge), 9, _hoisted_39)
                      ]),
                      vue$1.createElementVNode("div", null, [
                        vue$1.createElementVNode("button", {
                          type: "button",
                          class: "btn btn-primary btn-sm c-mp-dialog__busy",
                          disabled: !$data.acknowledged || $data.saving !== null,
                          onClick: _cache[8] || (_cache[8] = (...args) => $options.confirmCommunity && $options.confirmCommunity(...args))
                        }, [
                          $data.saving === "includeCommunityModules" ? (vue$1.openBlock(), vue$1.createElementBlock("i", _hoisted_41)) : vue$1.createCommentVNode("v-if", true),
                          vue$1.createTextVNode(
                            vue$1.toDisplayString($options.labels.confirm),
                            1
                            /* TEXT */
                          )
                        ], 8, _hoisted_40)
                      ])
                    ])
                  ])) : vue$1.createCommentVNode("v-if", true)
                ])
              ])
            ],
            2
            /* CLASS */
          ),
          $data.settingsError ? (vue$1.openBlock(), vue$1.createElementBlock(
            "p",
            _hoisted_42,
            vue$1.toDisplayString($data.settingsError),
            1
            /* TEXT */
          )) : vue$1.createCommentVNode("v-if", true),
          $props.moduleAdministrationUrl ? (vue$1.openBlock(), vue$1.createElementBlock("a", {
            key: 1,
            href: $props.moduleAdministrationUrl,
            class: "c-mp-settings__admin"
          }, [
            _cache[12] || (_cache[12] = vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-apps",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )),
            vue$1.createTextVNode(
              vue$1.toDisplayString($options.labels.moduleAdministration),
              1
              /* TEXT */
            )
          ], 8, _hoisted_43)) : vue$1.createCommentVNode("v-if", true)
        ], 8, _hoisted_22), [
          [vue$1.vShow, $data.tab === "settings"]
        ])
      ]),
      _: 1
      /* STABLE */
    }, 8, ["title", "onUpdate:show"]);
  }
  const SettingsDialog = /* @__PURE__ */ _export_sfc(_sfc_main$1, [["render", _sfc_render$1]]);
  function createUpdateQueue(ids, update) {
    let stopped = false;
    return {
      async run() {
        const failed = [];
        for (const id of ids) {
          if (stopped) {
            break;
          }
          try {
            await update(id);
          } catch (error) {
            failed.push(id);
          }
        }
        return { stopped, failed };
      },
      stop() {
        stopped = true;
      }
    };
  }
  const IDLE = Object.freeze({ error: null, update: null });
  const UPDATED_MS = 1200;
  const REMOVE_MS = 360;
  const UPDATE_BUSY = ["running", "success", "removing"];
  const reducedMotion = () => typeof window.matchMedia === "function" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  const focusLost = () => !document.activeElement || document.activeElement === document.body || !document.activeElement.isConnected;
  const wait = (ms) => new Promise((resolve) => {
    setTimeout(resolve, ms);
  });
  const _sfc_main = {
    name: "MarketplaceBrowser",
    i18nCategories: ["MarketplaceModule.base", "base"],
    components: { CoreVersionInfo, InstallDialog, ModuleCard, SettingsDialog },
    props: {
      filters: { type: Array, required: true },
      settings: { type: Object, required: true },
      urls: { type: Object, required: true },
      installationId: { type: String, default: "" }
    },
    data() {
      return {
        settingsState: { ...this.settings },
        updateCount: 0,
        cardStates: {},
        dialog: null,
        updateQueue: null,
        // Set synchronously when "Update all" starts (before its candidates are fetched),
        // so a second click cannot start a second queue.
        updatingAll: false,
        // "Cancel all" was pressed: the running update finishes, nothing further starts.
        stopRequested: false,
        // The search the shown list was loaded with, marked in the cards.
        query: "",
        // Every opened dialog gets a fresh key: two dialogs of the same type in a row are
        // two component instances, never one reusing the other's state.
        dialogSeq: 0,
        // `{ latest, updateUrl }` once humhub.com reported a newer HumHub version.
        coreUpdate: null,
        // The live region's text: the result of the last finished update.
        announcement: ""
      };
    },
    computed: {
      anyUpdateRunning() {
        return Object.values(this.cardStates).some((state) => UPDATE_BUSY.includes(state.update));
      },
      // An install dialog is open: its installation may run any moment (one action at a time).
      installOpen() {
        return this.dialog !== null && this.dialog.type === "install";
      },
      updateAllBlocked() {
        return this.updatingAll || this.anyUpdateRunning || this.installOpen;
      },
      // While "Update all" runs the button is its "Cancel all" control — usable once the
      // queue exists (its candidates are fetched) and until cancelling was requested.
      updateAllDisabled() {
        return this.updatingAll ? this.updateQueue === null || this.stopRequested : this.updateAllBlocked;
      },
      showUpdateAll() {
        return this.updatingAll || this.updateCount > 0;
      },
      cardsLocked() {
        return this.updateQueue !== null || this.updatingAll || this.installOpen || this.anyUpdateRunning;
      },
      listUrl() {
        return vue.apiUrl("marketplace/module");
      },
      labels() {
        return {
          title: vue.i18n.t("MarketplaceModule.base", "Marketplace"),
          settings: vue.i18n.t("MarketplaceModule.base", "Marketplace Settings"),
          updateAll: vue.i18n.t("MarketplaceModule.base", "Update All"),
          updateAllLabel: vue.i18n.t("MarketplaceModule.base", "Update all modules"),
          cancelAll: vue.i18n.t("MarketplaceModule.base", "Cancel All"),
          cancelAllLabel: vue.i18n.t("MarketplaceModule.base", "Cancel all module updates")
        };
      }
    },
    created() {
      fetchCoreVersion().then((info) => {
        if (info && info.updateAvailable === true && info.latest) {
          this.coreUpdate = { latest: String(info.latest), updateUrl: info.updateUrl || null };
        }
      }).catch((response) => {
        vue.log.error(response);
      });
    },
    methods: {
      stateOf(id) {
        return this.cardStates[id] || IDLE;
      },
      setState(id, patch) {
        this.cardStates[id] = { ...this.stateOf(id), ...patch };
      },
      replaceModule(module, keepFocus = this.hasFocusIn(module.id)) {
        var _a;
        (_a = this.$refs.directory) == null ? void 0 : _a.replaceItem(module.id, module);
        if (keepFocus) {
          this.focusCard(module.id);
        }
      },
      cardCell(id) {
        return this.$el ? [...this.$el.querySelectorAll("[data-id]")].find((cell) => cell.dataset.id === String(id)) || null : null;
      },
      hasFocusIn(id) {
        const cell = this.cardCell(id);
        return Boolean(cell && cell.contains(document.activeElement));
      },
      // Once rendered: the card's primary action, or — disabled ("Installed") — its title link
      // or its secondary link.
      focusCard(id) {
        this.$nextTick(() => {
          const cell = this.cardCell(id);
          if (!cell) {
            return;
          }
          const target = [".c-module-card__action", "a.c-module-card__title", ".c-module-card__secondary"].map((selector) => cell.querySelector(selector)).find((element) => element && !element.disabled);
          if (target) {
            target.focus();
          }
        });
      },
      announce(message) {
        this.announcement = message;
      },
      reload() {
        var _a;
        return (_a = this.$refs.directory) == null ? void 0 : _a.reload();
      },
      onLoaded({ meta, values }) {
        this.updateCount = meta.updateCount || 0;
        this.query = values && typeof values.q === "string" ? values.q.trim() : "";
      },
      filterByType(tag) {
        this.$refs.directory.setFilter("tag", tag);
      },
      openDialog(type, payload2 = {}) {
        this.dialog = { type, ...payload2, key: ++this.dialogSeq };
      },
      closeDialog() {
        this.dialog = null;
      },
      openSettings(tab = "licenses") {
        if (!this.installOpen) {
          this.openDialog("settings", { tab });
        }
      },
      installBlocked() {
        return this.updatingAll || this.updateQueue !== null || this.installOpen || this.anyUpdateRunning;
      },
      openInstall(module, step) {
        if (this.installBlocked()) {
          return false;
        }
        this.openDialog("install", { module, step, registered: false, installed: false });
        return true;
      },
      install(module) {
        return this.openInstall(module, "confirm");
      },
      buy(module) {
        return this.openInstall(module, "buy");
      },
      onInstalled(module) {
        this.dialog.installed = true;
        this.replaceModule(module);
      },
      // A key registered in the dialog without the installation succeeding: the list behind
      // it (Buy → Install) is out of date.
      // An installed card's Install button went away with the installation: the focus the
      // dialog hands back goes to the new card instead.
      closeInstall() {
        const { module, registered, installed } = this.dialog || {};
        const stale = registered && !installed;
        this.closeDialog();
        if (stale) {
          this.reload();
        }
        if (installed) {
          this.$nextTick(() => {
            if (focusLost()) {
              this.focusCard(module.id);
            }
          });
        }
      },
      async update(module) {
        const hadFocus = this.hasFocusIn(module.id);
        this.setState(module.id, { update: "running", error: null });
        let updated;
        try {
          updated = await updateModule(module.id);
        } catch (response) {
          const error = errorMessage(response, vue.i18n.t("MarketplaceModule.base", "Update failed"));
          this.setState(module.id, { update: "failed", error });
          this.announce(`${module.name}: ${error}`);
          vue.log.error(response);
          throw response;
        }
        await this.presentUpdated(module.id, updated, hadFocus);
        this.announce(this.updatedMessage(updated));
        return updated;
      },
      updatedMessage(updated) {
        return vue.i18n.t("MarketplaceModule.base", 'Module "{moduleName}" has been updated to version {newVersion} successfully.', {
          moduleName: updated.name,
          newVersion: updated.installedVersion
        });
      },
      // "Updated!" on the card, its remove animation, then the updated module fading in —
      // at once under `prefers-reduced-motion`. `hadFocus`: the card had the focus when the
      // update started (the disabled Update button may have lost it since).
      async presentUpdated(id, updated, hadFocus = false) {
        if (!reducedMotion()) {
          this.setState(id, { update: "success" });
          await wait(UPDATED_MS);
          this.setState(id, { update: "removing" });
          await wait(REMOVE_MS);
        }
        this.replaceModule(updated, this.hasFocusIn(id) || hadFocus && focusLost());
        this.setState(id, { update: "done" });
      },
      async updateOne(module) {
        if (this.installBlocked()) {
          return;
        }
        try {
          const updated = await this.update(module);
          this.updateCount = Math.max(0, this.updateCount - 1);
          vue.status("success", this.updatedMessage(updated));
        } catch (response) {
          vue.status("error", this.stateOf(module.id).error);
        }
      },
      async startUpdateAll() {
        if (this.updateAllBlocked) {
          return;
        }
        this.updatingAll = true;
        this.stopRequested = false;
        try {
          let candidates;
          try {
            candidates = await fetchModules({ status: "update" });
          } catch (response) {
            vue.status("error", errorMessage(response, vue.i18n.t("MarketplaceModule.base", "Update failed")));
            return;
          }
          const byId = Object.fromEntries(candidates.map((module) => [module.id, module]));
          candidates.forEach((module) => this.setState(module.id, { update: "pending", error: null }));
          this.updateQueue = createUpdateQueue(candidates.map((module) => module.id), (id) => this.update(byId[id]));
          const { stopped, failed } = await this.updateQueue.run();
          this.updateQueue = null;
          candidates.forEach((module) => {
            if (this.stateOf(module.id).update === "pending") {
              this.setState(module.id, { update: null });
            }
          });
          if (!stopped) {
            if (failed.length) {
              vue.status("warning", vue.i18n.t("MarketplaceModule.base", "Some modules could not be updated."));
            } else {
              vue.status("success", vue.i18n.t("MarketplaceModule.base", "Update successful"));
            }
          }
          await this.reload();
        } finally {
          this.updateQueue = null;
          this.updatingAll = false;
          this.stopRequested = false;
        }
      },
      stopUpdateAll() {
        if (this.updateQueue) {
          this.stopRequested = true;
          this.updateQueue.stop();
        }
      },
      onSettingsChanged(settings) {
        this.settingsState = settings;
        this.reload();
        this.$refs.directory.reloadFilterOptions();
      },
      onLicenceInstall(module) {
        if (this.installBlocked()) {
          return;
        }
        this.closeDialog();
        this.install(module);
      }
    }
  };
  const _hoisted_1 = { class: "c-marketplace" };
  const _hoisted_2 = ["aria-label", "title"];
  const _hoisted_3 = ["aria-hidden"];
  const _hoisted_4 = ["aria-label", "title", "tabindex", "disabled"];
  const _hoisted_5 = {
    class: "visually-hidden",
    role: "status",
    "aria-live": "polite"
  };
  function _sfc_render(_ctx, _cache, $props, $setup, $data, $options) {
    const _component_CoreVersionInfo = vue$1.resolveComponent("CoreVersionInfo");
    const _component_ModuleCard = vue$1.resolveComponent("ModuleCard");
    const _component_CardDirectory = vue$1.resolveComponent("CardDirectory");
    const _component_InstallDialog = vue$1.resolveComponent("InstallDialog");
    const _component_SettingsDialog = vue$1.resolveComponent("SettingsDialog");
    return vue$1.openBlock(), vue$1.createElementBlock("div", _hoisted_1, [
      vue$1.createVNode(_component_CardDirectory, {
        ref: "directory",
        url: $options.listUrl,
        title: $options.labels.title,
        filters: $props.filters,
        "page-size": 24,
        "meta-keys": ["updateCount"],
        "id-prefix": "marketplace-filter",
        onLoaded: $options.onLoaded
      }, vue$1.createSlots({
        actions: vue$1.withCtx(() => [
          vue$1.createElementVNode("button", {
            type: "button",
            class: "btn btn-secondary c-icon-button c-marketplace__settings",
            "aria-label": $options.labels.settings,
            title: $options.labels.settings,
            onClick: _cache[0] || (_cache[0] = ($event) => $options.openSettings())
          }, [..._cache[3] || (_cache[3] = [
            vue$1.createElementVNode(
              "i",
              {
                class: "ti ti-settings",
                "aria-hidden": "true"
              },
              null,
              -1
              /* CACHED */
            )
          ])], 8, _hoisted_2),
          vue$1.createElementVNode("span", {
            class: vue$1.normalizeClass(["c-marketplace__update-all-slot", { "is-collapsed": !$options.showUpdateAll }]),
            "aria-hidden": $options.showUpdateAll ? null : "true"
          }, [
            vue$1.createElementVNode("button", {
              type: "button",
              class: vue$1.normalizeClass(["btn c-icon-button c-marketplace__update-all", $data.updatingAll ? "btn-warning is-running" : "btn-accent"]),
              "aria-label": $data.updatingAll ? $options.labels.cancelAllLabel : $options.labels.updateAllLabel,
              title: $data.updatingAll ? $options.labels.cancelAll : $options.labels.updateAll,
              tabindex: $options.showUpdateAll ? null : -1,
              disabled: $options.updateAllDisabled,
              onClick: _cache[1] || (_cache[1] = ($event) => $data.updatingAll ? $options.stopUpdateAll() : $options.startUpdateAll())
            }, [..._cache[4] || (_cache[4] = [
              vue$1.createElementVNode(
                "i",
                {
                  class: "ti ti-refresh c-marketplace__spin",
                  "aria-hidden": "true"
                },
                null,
                -1
                /* CACHED */
              )
            ])], 10, _hoisted_4)
          ], 10, _hoisted_3)
        ]),
        card: vue$1.withCtx(({ item }) => [
          vue$1.createVNode(_component_ModuleCard, {
            module: item,
            error: $options.stateOf(item.id).error,
            "update-state": $options.stateOf(item.id).update,
            locked: $options.cardsLocked,
            query: $data.query,
            "professional-edition-url": $props.urls.professionalEdition,
            onInstall: ($event) => $options.install(item),
            onBuy: ($event) => $options.buy(item),
            onUpdate: ($event) => $options.updateOne(item),
            onFilterType: $options.filterByType
          }, null, 8, ["module", "error", "update-state", "locked", "query", "professional-edition-url", "onInstall", "onBuy", "onUpdate", "onFilterType"])
        ]),
        _: 2
        /* DYNAMIC */
      }, [
        $data.coreUpdate ? {
          name: "notice",
          fn: vue$1.withCtx(() => [
            vue$1.createVNode(_component_CoreVersionInfo, {
              latest: $data.coreUpdate.latest,
              "update-url": $data.coreUpdate.updateUrl
            }, null, 8, ["latest", "update-url"])
          ]),
          key: "0"
        } : void 0
      ]), 1032, ["url", "title", "filters", "onLoaded"]),
      vue$1.createElementVNode(
        "div",
        _hoisted_5,
        vue$1.toDisplayString($data.announcement),
        1
        /* TEXT */
      ),
      $data.dialog && $data.dialog.type === "install" ? (vue$1.openBlock(), vue$1.createBlock(_component_InstallDialog, {
        key: $data.dialog.key,
        module: $data.dialog.module,
        "initial-step": $data.dialog.step,
        onRegistered: _cache[2] || (_cache[2] = ($event) => $data.dialog.registered = true),
        onInstalled: $options.onInstalled,
        onActivated: $options.replaceModule,
        onClose: $options.closeInstall
      }, null, 8, ["module", "initial-step", "onInstalled", "onActivated", "onClose"])) : $data.dialog && $data.dialog.type === "settings" ? (vue$1.openBlock(), vue$1.createBlock(_component_SettingsDialog, {
        key: $data.dialog.key,
        settings: $data.settingsState,
        "installation-id": $props.installationId,
        "module-administration-url": $props.urls.moduleAdministration,
        "initial-tab": $data.dialog.tab,
        "install-locked": $options.installBlocked(),
        onRegistered: $options.reload,
        onSettingsChanged: $options.onSettingsChanged,
        onInstall: $options.onLicenceInstall,
        onClose: $options.closeDialog
      }, null, 8, ["settings", "installation-id", "module-administration-url", "initial-tab", "install-locked", "onRegistered", "onSettingsChanged", "onInstall", "onClose"])) : vue$1.createCommentVNode("v-if", true)
    ]);
  }
  const C0 = /* @__PURE__ */ _export_sfc(_sfc_main, [["render", _sfc_render]]);
  vue.register("MarketplaceBrowser", C0);
})(humhub.modules.vue, Vue);
//# sourceMappingURL=humhub.marketplace.vue.js.map
