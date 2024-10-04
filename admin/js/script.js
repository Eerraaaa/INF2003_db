// Run when the DOM content is fully loaded
document.addEventListener("DOMContentLoaded", function() {
  const sidenav = document.getElementById("sidenav");

  // Check the localStorage for the sidenav state
  const isSidenavOpen = localStorage.getItem("sidenavOpen");

  // Set the sidenav width based on the saved state
  if (isSidenavOpen === "true") {
      sidenav.style.width = "250px"; // Open
  } else {
      sidenav.style.width = "0px"; // Closed
  }

  // Define the openside function
  window.openside = function() {
      if (sidenav.style.width === "0px" || sidenav.style.width === "") {
          sidenav.style.width = "250px"; // Open
          localStorage.setItem("sidenavOpen", "true"); // Save state as open
      } else {
          sidenav.style.width = "0px"; // Close
          localStorage.setItem("sidenavOpen", "false"); // Save state as closed
      }
  };
});
