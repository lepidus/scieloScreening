/**
 * SciELO Screening Plugin - Main JavaScript Entry Point
 *
 * This file registers Vue components and extends the workflow store
 * to add the screening info tab in the editorial workflow.
 */

import ScreeningInfo from "./components/ScreeningInfo.vue";

// Register the component globally
pkp.registry.registerComponent("ScreeningInfo", ScreeningInfo);

/**
 * Extend the workflow store to add the screening menu item
 * and render the ScreeningInfo component
 */
pkp.registry.storeExtend("workflow", (piniaContext) => {
  const workflowStore = piniaContext.store;
  const { useLocalize } = pkp.modules.useLocalize;
  const { t } = useLocalize();

  // Add "SciELO Screening" menu item to workflow navigation
  workflowStore.extender.extendFn("getMenuItems", (menuItems, args) => {
    return [
      ...menuItems,
      {
        key: "scieloScreening",
        label: t("plugins.generic.scieloScreening.info.name"),
        state: { primaryMenuItem: "scieloScreening" },
      },
    ];
  });

  // Render the ScreeningInfo component when the screening menu is selected
  workflowStore.extender.extendFn("getPrimaryItems", (primaryItems, args) => {
    if (args?.selectedMenuState?.primaryMenuItem === "scieloScreening") {
      return [
        {
          component: "ScreeningInfo",
          props: { submission: args.submission },
        },
      ];
    }
    return primaryItems;
  });
});
