(function($){
  // Initialize Select2
  $('.dt-select').select2();

  // Get the DataTables saved state
  const defaultDtState = {
    "time": Date.now(),
    "start":0,
    "length":20,
    "order":[[0,"asc"]],
    "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true},
    "columns":[
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":false,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      },
      {
        "visible":true,
        "search":{"search":"","smart":true,"regex":false,"caseInsensitive":true}
      }
    ]
  };
  let dtState = window.localStorage.getItem('DataTables_datatable_/plans/');
  let dtStateObj = {};
  if( dtState === null ){
    console.log(`dtState is null.`);
    dtStateObj = defaultDtState;
  } else {
    dtStateObj = JSON.parse(dtState);
  }
  console.log(`dtStateObj = `, dtStateObj);

  // Listener for the "Select a State..." drop down
  $('#plan-by-state-selector').on('change','#states',function(e){
    const selection = $(this).val();
    const valueArray = selection.split('-');
    const state = valueArray[0];
    const termId = valueArray[1];

    // Set the `State`
    dtStateObj.columns[1].search.search = state;
    // Set the `Product`
    dtStateObj.columns[2].search.search = wpvars.product;
    // Update the DataTables saved state:
    window.localStorage.setItem( 'DataTables_datatable_/plans/', JSON.stringify(dtStateObj) );
  });

  // Click handler for the "View Plans" button
  $('#plan-by-state-selector').on('click','#selector-button',function(e){
    e.preventDefault();
    window.location.href = wpvars.product_finder_url;
  });
})(jQuery);