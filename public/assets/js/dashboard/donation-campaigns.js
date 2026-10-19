function enforceEndDateAfterStart() {
  const $startDate = $("#startDate");
  const $endDate = $("#endDate");

  $startDate.on("change", function () {
    const start = $(this).val();
    const end = $endDate.val();

    if (end && end < start) {
      $endDate.val("");
    }

    $endDate.attr("min", start);
  });
}

enforceEndDateAfterStart();
