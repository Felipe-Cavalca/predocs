/*=========================================================================================
	File Name: ext-component-tour.js
	Description: extra component tour for webpage guide
	----------------------------------------------------------------------------------------
	Item Name: Frest HTML Admin Template
	Version: 1.0
	Author: Pixinvent
	Author URL: hhttp://www.themeforest.net/user/pixinvent
==========================================================================================*/

$(document).ready(function () {
  // tour initialize
  displayTour();
  $(window).resize(displayTour)
  var tour = new Shepherd.Tour({
    classes: 'shadow-md bg-purple-dark',
    scrollTo: true
  })

  // tour step 1
  tour.addStep('step-1', {
    text: 'Here is page title.',
    attachTo: '.breadcrumbs-top .content-header-title bottom',
    buttons: [

      {
        text: "Skip",
        action: tour.complete
      },
      {
        text: 'Next',
        action: tour.next
      },
    ]
  });
  // tour step 2
  tour.addStep('step-2', {
    text: 'Check your notifications from here.',
    attachTo: '.dropdown-notification .bx-bell bottom',
    buttons: [

      {
        text: "Skip",
        action: tour.complete
      },

      {
        text: "previous",
        action: tour.back
      },
      {
        text: 'Next',
        action: tour.next
      },
    ]
  });
  // tour step 3
  tour.addStep('step-3', {
    text: 'Click here for user options.',
    attachTo: '.dropdown-user-link img bottom',
    buttons: [

      {
        text: "Skip",
        action: tour.complete
      },

      {
        text: "previous",
        action: tour.back
      },
      {
        text: 'Next',
        action: tour.next
      },
    ]
  });
  // tour step 4
  tour.addStep('step-4', {
    text: 'Buy this awesomeness at affordable price!',
    attachTo: '.buy-now bottom',
    buttons: [

      {
        text: "previous",
        action: tour.back
      },

      {
        text: "Finish",
        action: tour.complete
      },
    ]
  });

  // function to remove tour on small screen
  function displayTour() {
    window.resizeEvt;
    if ($(window).width() > 576) {
      $('#tour').on("click", function () {
        clearTimeout(window.resizeEvt);
        tour.start();
      })
    }
    else {
      $('#tour').on("click", function () {
        clearTimeout(window.resizeEvt);
        tour.cancel()
        window.resizeEvt = setTimeout(function () {
          alert("Tour only works for large screens!");
        }, 250);;
      })
    }
  }
});
