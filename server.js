// Node.js 18 veya daha yeni sürüm
const express = require('express');
const app = express();
//const port = process.env.PORT || 8080

app.get('/', async (req, res) => {
  const targetUrl = req.query.url; // Renamed for clarity from 'url'

  if (!targetUrl) {
    // If the 'url' query parameter is missing, send a 400 Bad Request error
    return res.status(400).send("Hata: 'url' query parametresi eksik. Lütfen ?url=BIR_URL_DEGERI şeklinde bir URL sağlayın.");
  }

  try {
    // Attempt to fetch the content from the provided targetUrl
    // Ensure the targetUrl is a full, valid URL (e.g., "https://example.com")
    const response = await fetch(targetUrl);
    const html = await response.text();
    res.send(html);
  } catch (error) {
    // Handle errors during the fetch operation, including invalid URLs passed to fetch
    console.error(`Proxy hatası ${targetUrl} için:`, error.message); // Log the error server-side
    res.status(500).send(`Proxy tarafında hata oluştu: ${error.message}. İstenen URL: ${targetUrl}`);
  }
});

app.listen(8080, () => console.log('Proxy sunucusu port 8080 üzerinde çalışıyor'));
