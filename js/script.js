// This script assumes it's running in a browser environment
// and that the proxy server (nodeServer.js) is running on http://localhost:3000

async function fetchAndDisplayProductInfo() {
    const targetSiteUrl = 'https://www.walmart.com/ip/Chicken-of-the-Sea-Wild-Caught-Sardines-in-Lemon-Sauce-3-75-oz-Can/2554525386';
    
    // Construct the URL for your proxy server.
    // The targetSiteUrl needs to be URI encoded to be safely passed as a query parameter.
    const proxyRequestUrl = `http://localhost:8080/?url=${encodeURIComponent(targetSiteUrl)}`;

    console.log("Client attempting to fetch from proxy with URL:", proxyRequestUrl); // Log the URL

    
    const sonucDisplayElement = document.getElementById('sonuc');
    if (!sonucDisplayElement) {
        console.error("Hata: Sayfada 'sonuc' ID'li HTML elementi bulunamadı.");
        return;
    }
    sonucDisplayElement.innerHTML = 'Veri yükleniyor, lütfen bekleyin...';

    try {
        // Fetch data from your proxy server
        const response = await fetch(proxyRequestUrl);
        
        if (!response.ok) {
            // If the proxy server returned an error (e.g., 400, 500)
            const errorTextFromServer = await response.text();
            throw new Error(`Proxy sunucusundan hata (${response.status}): ${errorTextFromServer}`);
        }
        
        const htmlContentFromProxy = await response.text();
        
        const parser = new DOMParser();
        const doc = parser.parseFromString(htmlContentFromProxy, 'text/html'); // Corrected: use htmlContentFromProxy
        
        console.log(doc); // Log the parsed document for debugging purposes
        
        const hedefElement = doc.querySelector('span.flex-column'); // Your target selector
        if (hedefElement) {
            sonucDisplayElement.innerHTML = hedefElement.innerHTML;
        } else {
            sonucDisplayElement.innerHTML = "Hedeflenen element (.flex-column) sayfada bulunamadı. Lütfen seçiciyi veya sayfa yapısını kontrol edin.";
            console.warn("Hedef element (.flex-column) bulunamadı. Sayfa içeriğini ve seçiciyi doğrulayın.");
        }
    } catch (error) {
        // Catches network errors for the fetch to the proxy, or other JavaScript errors
        console.error('İstemci tarafında veri alınırken veya işlenirken hata oluştu:', error);
        sonucDisplayElement.innerHTML = `Bir hata meydana geldi: ${error.message}`;
    }
}

// Call the function to initiate the process.
// This can be triggered by an event (e.g., button click) or run on page load.
// Using an async IIFE (Immediately Invoked Function Expression) to allow 'await' - Removed for button click.
(async () => {
    await fetchAndDisplayProductInfo();
})();
