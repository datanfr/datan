$(document).ready(function () {
  const alertDiv = $(".alert-warning");
  const checkboxes = $('input[type="checkbox"]');
  const allCategoriesCheckbox = $("#cat-all");
  const mainCategoriesCount = $(".category-checkbox").length;
  const subcategoriesCount = $(".subcategory-checkbox").length;

  /*
########## FUNCIONS ##########
*/

  function initializeIframeUrl() {
    const slugElement = $("#iframe-wrapper");
    if (slugElement.length) {
      const slug = slugElement.data("slug");

      return window.location.origin + "/iframe/depute/" + slug;
    }
    return "";
  }

  function getSelectedCategories() {
    const categories = [];
    if (!allCategoriesCheckbox.prop("checked")) {
      $(".category-checkbox:checked").each(function () {
        categories.push($(this).val());
      });
    }
    return categories;
  }

  function getSelectedSubcategories() {
    const subcategories = [];
    $(".subcategory-checkbox:checked").each(function () {
      subcategories.push("comportement-politique." + $(this).val());
    });
    return subcategories;
  }

  function getSelectedOptions() {
    const options = [];
    if ($("#hideMainTitle").prop("checked")) {
      options.push("main-title=hide");
    }
    if ($("#hideSecondaryTitle").prop("checked")) {
      options.push("secondary-title=hide");
    }
    const personMode = $('input[name="person-mode"]:checked').val();
    if (personMode === "first") {
      options.push("first-person=true");
    }
    return options;
  }

  function buildIframeUrl() {
    let iframeUrl = initializeIframeUrl();
    const categories = getSelectedCategories();
    const subcategories = getSelectedSubcategories();
    const options = getSelectedOptions();
    const politicalBehaviorCategory = $("#cat6");
    const params = [];
    let finalCategories = [];

    if (allCategoriesCheckbox.prop("checked")) {
      if (options.length > 0) {
        params.push(...options);
      }
      if (params.length > 0) {
        iframeUrl += "?" + params.join("&");
      }
      return iframeUrl;
    }

    if (categories.length === mainCategoriesCount) {
      allCategoriesCheckbox.prop("checked", true);
      toggleCategoryCheckboxes(true);
      toggleSubCategoryCheckboxes(true);
      if (options.length > 0) {
        params.push(...options);
        iframeUrl += "?" + params.join("&");
      }
      return iframeUrl;
    }

    finalCategories = [...categories];

    subcategories.forEach(function (subcategory) {
      if (politicalBehaviorCategory.prop("checked")) {
        return;
      }
      finalCategories.push(subcategory);
    });

    if (subcategories.length === subcategoriesCount) {
      finalCategories = finalCategories.filter(function (cat) {
        return !cat.startsWith("comportement-politique.");
      });
      politicalBehaviorCategory.prop("checked", true);
      toggleSubCategoryCheckboxes(true);
      if (!finalCategories.includes("comportement-politique")) {
        finalCategories.push("comportement-politique");
      }
    }

    if (finalCategories.length > 0) {
      params.push("categories=" + finalCategories.join(","));
    }
    if (options.length > 0) {
      params.push(...options);
    }
    if (params.length > 0) {
      iframeUrl += "?" + params.join("&");
    }

    return iframeUrl;
  }

  function updateIframeAndCode(iframeUrl) {
    const categories = getSelectedCategories();
    const subcategories = getSelectedSubcategories();
    const iframePreview = $("#iframePreview");
    const iframeCodeElement = $("#iframeCode");

    if (allCategoriesCheckbox.prop("checked") || categories.length > 0 || subcategories.length > 0) {
      iframePreview.attr("src", iframeUrl);
      const iframeCode = `<iframe src="${iframeUrl}" width="400" height="600" frameborder="0"></iframe>`;
      iframeCodeElement.val(iframeCode);
    } else {
      iframePreview.attr("src", "");
      iframeCodeElement.val("");
    }
  }

  function handlePreview() {
    const iframeUrl = buildIframeUrl();
    const shouldShowAlert = checkboxes
      .toArray()
      .some((cb) => $(cb).prop("checked") && ($(cb).val().includes("all") || $(cb).val().includes("explication")));
    if (alertDiv.length) {
      alertDiv.toggle(shouldShowAlert);
    }
    updateIframeAndCode(iframeUrl);
  }

  function toggleCategoryCheckboxes(disable) {
    $(".category-checkbox").each(function () {
      $(this).prop("checked", disable);
      $(this).prop("disabled", disable);
    });
  }

  function toggleSubCategoryCheckboxes(disable) {
    $(".subcategory-checkbox").each(function () {
      $(this).prop("checked", disable);
      $(this).prop("disabled", disable);
    });
  }

  function handlePoliticalBehaviorCategory() {
    const politicalBehaviorCategory = $("#cat6");
    politicalBehaviorCategory.on("change", function () {
      if ($(this).prop("checked")) {
        toggleSubCategoryCheckboxes(true);
      } else {
        toggleSubCategoryCheckboxes(false);
      }
    });
  }

  /*
########## INIT ##########
*/

  let iframeUrl = "";
  if (alertDiv.length) {
    alertDiv.hide();
  }

  $('input[name="person-mode"]').on("change", function () {
    const iframeUrl = buildIframeUrl();
    updateIframeAndCode(iframeUrl);
  });

  allCategoriesCheckbox.on("change", function () {
    if ($(this).prop("checked")) {
      toggleCategoryCheckboxes(true);
      toggleSubCategoryCheckboxes(true);
    } else {
      toggleCategoryCheckboxes(false);
      toggleSubCategoryCheckboxes(false);
    }
  });

  handlePoliticalBehaviorCategory();

  allCategoriesCheckbox.prop("checked", true);
  toggleCategoryCheckboxes(true);
  toggleSubCategoryCheckboxes(true);

  const shouldShowAlert = checkboxes
    .toArray()
    .some((cb) => $(cb).prop("checked") && ($(cb).val().includes("all") || $(cb).val().includes("explication")));
  if (alertDiv.length) {
    alertDiv.toggle(shouldShowAlert);
  }

  iframeUrl = initializeIframeUrl() + "?first-person=true";

  updateIframeAndCode(iframeUrl);

  checkboxes.each(function () {
    $(this).on("change", handlePreview);
  });
});
