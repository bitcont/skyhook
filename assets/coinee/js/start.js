$(document).ready(function() {

  // replace the fullscreen behavior with two divs

  function goToAccountPage() {
		window.location.replace('/account');
	}

  function goToCoineeStartPage() {
    window.location.replace('/coinee-start');
  }
	
	function bind() {
		$('.buy-btc').on('touchstart mousedown', goToAccountPage);
    $('.coinee').on('touchstart mousedown', goToCoineeStartPage);
	}
	
	function unbind() {
    $('.buy-btc').off('touchstart mousedown', goToAccountPage);
    $('.coinee').off('touchstart mousedown', goToCoineeStartPage);
	}

  $(document.body).off('touchstart mousedown');
  bind();
	
	HelpPanel.onOpen = unbind;
	HelpPanel.onClose = bind;

});
