// Add your custom JavaScript code here
// For example, you can add smooth scrolling to the page
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();

        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Toggle between upload and download history
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        document.querySelectorAll('.history-content').forEach(content => {
            content.classList.remove('active');
        });

        document.getElementById(this.dataset.tab).classList.add('active');
    });
});



// Handle form submission for viewing shared files
document.querySelector('.shared-files-form').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent default form submission

    const emailShared = this.querySelector('.email-input').value; // Get the entered email
    const sharedFilesList = document.querySelector('.shared-files-list'); // Get the shared files list

    // Send a POST request to fetch shared files
    fetch('index.php', {
        method: 'POST',
        body: new URLSearchParams({
            email_shared: emailShared
        }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json()) // Parse response as JSON
    .then(sharedFiles => {
        // Clear previous search results
        sharedFilesList.innerHTML = '';

        // Display shared files
        sharedFiles.forEach(sharedFile => {
            const fileElement = document.createElement('div');
            fileElement.classList.add('shared-file');
            fileElement.innerHTML = `
                <p>Key: ${sharedFile.file_key}</p>
                <p>File: ${sharedFile.file_name}</p>
                <a href='${sharedFile.file_path}' class='btn download-btn'>Download</a>
            `;
            sharedFilesList.appendChild(fileElement);
        });
    })
    .catch(error => console.error('Error fetching shared files:', error));
});















document.querySelectorAll('.social-icon').forEach(icon => {
    icon.addEventListener('mouseover', () => {
        icon.classList.add('hover-effect');
    });

    icon.addEventListener('mouseout', () => {
        icon.classList.remove('hover-effect');
    });
});















const dragDropArea = document.getElementById('dragDropArea');
const fileInput = document.getElementById('fileInput');

dragDropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dragDropArea.classList.add('drag-over');
});

dragDropArea.addEventListener('dragleave', () => {
    dragDropArea.classList.remove('drag-over');
});

dragDropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dragDropArea.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        const fileName = files[0].name;
        dragDropArea.querySelector('.drag-drop-text').textContent = fileName;
    }
});

// Simulate a click on the file input when the drag and drop area is clicked
dragDropArea.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', () => {
    const fileName = fileInput.files[0].name;
    if (fileName) {
        dragDropArea.querySelector('.drag-drop-text').textContent = fileName;
    }
});








function removeMessage(message) {
    setTimeout(() => {
        message.remove();
    }, 3500); // 3500 milliseconds = 3 seconds + 0.5 seconds (animation delay)
}

// Function to create a new message element
function createMessage(messageText, messageType) {
    const message = document.createElement('div');
    message.textContent = messageText;
    message.classList.add('message', messageType);

    // Append the message to the body
    document.body.appendChild(message);

    // Remove the message after a certain duration
    removeMessage(message);
}

// Function to display a success message
function showSuccessMessage(messageText) {
    createMessage(messageText, 'success-message');
}

// Function to display an error message
function showErrorMessage(messageText) {
    createMessage(messageText, 'error-message');
}

// Example usage:
// showSuccessMessage('Success! Your action was completed.');
// showErrorMessage('Error! Something went wrong.');

// CSS styles remain the same as provided in your code





















// Function to set a cookie
function setCookie(name, value, days) {
    var expires = "";
    if (days) {
      var date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
  }
  
  // Function to get a cookie value
  function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
  }
  
  // Event listener for the "Accept Cookies" button
  document.getElementById('acceptCookiesBtn').addEventListener('click', function() {
    setCookie('cookiesAccepted', 'true', 365); // Set a cookie for 1 year
    document.getElementById('cookieConsent').style.display = 'none'; // Hide the consent banner
  });
  
  // Event listener for the "Manage Cookies" button
  document.getElementById('manageCookiesBtn').addEventListener('click', function() {
    document.getElementById('cookieSettingsModal').style.display = 'flex'; // Show the cookie settings modal
  });
  
  // Event listener for the "Learn More" link
  document.getElementById('learnMoreLink').addEventListener('click', function(e) {
    e.preventDefault(); // Prevent the default link behavior
    window.open('/terms-and-conditions', '_blank'); // Open the terms and conditions in a new tab
  });
  
  // Event listener for the close button in the modal
  document.querySelector('#cookieSettingsModal .close').addEventListener('click', function() {
    document.getElementById('cookieSettingsModal').style.display = 'none'; // Hide the cookie settings modal
  });
  
  // Event listener for the "Save Settings" button
  document.getElementById('saveCookieSettings').addEventListener('click', function() {
    var analyticsChecked = document.querySelector('input[name="analytics"]').checked;
    var advertisingChecked = document.querySelector('input[name="advertising"]').checked;
    setCookie('analyticsAccepted', analyticsChecked, 365);
    setCookie('advertisingAccepted', advertisingChecked, 365);
    document.getElementById('cookieSettingsModal').style.display = 'none'; // Hide the cookie settings modal
  });
  
  // Check if the cookies have been accepted on page load
  window.onload = function() {
    var cookiesAccepted = getCookie('cookiesAccepted');
    if (cookiesAccepted === 'true') {
      document.getElementById('cookieConsent').style.display = 'none';
    }
  };





















// Initially hide the search container
document.querySelector('.search-container').style.display = 'none';

// Function to show or hide the search container based on visibility parameter
function toggleSearchContainer(visibility) {
    document.querySelector('.search-container').style.display = visibility ? 'block' : 'none';
}

// Event listener for upload history form submission
document.getElementById('uploadHistoryForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission
    
    var userEmail = document.querySelector('#uploadHistoryForm input[name="user_email"]').value.trim();
    if (userEmail !== '') {
        var formData = new FormData();
        formData.append('user_email', userEmail);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayUploadHistory(data);
            // Show the search container when upload history is displayed
            toggleSearchContainer(true);
        })
        .catch(error => console.error('Error:', error));
    }
});

// Function to handle upload history search
function searchUploadHistory() {
    var searchQuery = document.getElementById('uploadHistorySearchInput').value.trim();
    if (searchQuery !== '') {
        var formData = new FormData();
        formData.append('user_email', document.querySelector('#uploadHistoryForm input[name="user_email"]').value.trim());
        formData.append('search_query', searchQuery);
        
        fetch('index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            displayUploadHistory(data);
            // Show the search container when upload history is displayed
            toggleSearchContainer(true);
        })
        .catch(error => console.error('Error:', error));
    }
}

// Modify displayUploadHistory function to handle visibility of search container
function displayUploadHistory(history) {
    var uploadHistoryList = document.querySelector('.upload-history-list');
    uploadHistoryList.innerHTML = ''; // Clear existing content
    
    if (history.length > 0) {
        history.forEach(upload => {
            var uploadItem = document.createElement('div');
            uploadItem.classList.add('upload-item');
            uploadItem.innerHTML = `
                <p>File Name: ${upload.file_name}</p>
                <p>File Key: ${upload.file_key}</p>
                <p>Shared with: ${upload.shared_with}</p>
                <p>File Path: ${upload.file_path}</p>
            `;
            uploadHistoryList.appendChild(uploadItem);
        });
    } else {
        uploadHistoryList.innerHTML = '<p>No upload history found.</p>';
        // Hide the search container if no upload history found
        toggleSearchContainer(false);
    }
}



