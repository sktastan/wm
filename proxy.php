<?php
// proxy.php

// 'url' sorgu parametresini al
$targetUrl = isset($_GET['url']) ? $_GET['url'] : null;

// 'url' sorgu parametresi eksikse, 400 Bad Request hatası gönder
if (!$targetUrl) {
    http_response_code(400);
    // Hata mesajları için içerik türünü text/plain olarak ayarla
    header('Content-Type: text/plain; charset=utf-8');
    echo "Hata: 'url' query parametresi eksik. Lütfen ?url=BIR_URL_DEGERI şeklinde bir URL sağlayın.";
    exit;
}

// cURL oturumunu başlat
$ch = curl_init();

// cURL seçeneklerini ayarla
curl_setopt($ch, CURLOPT_URL, $targetUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Aktarımı bir dize olarak döndür
curl_setopt($ch, CURLOPT_HEADER, false);        // Başlığı çıktıya dahil etme (sadece gövdeyi istiyoruz)
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Yönlendirmeleri takip et (varsayılan olarak en fazla 5)
curl_setopt($ch, CURLOPT_MAXREDIRS, 10);        // Gerekirse maksimum yönlendirme sayısını artır
curl_setopt($ch, CURLOPT_USERAGENT, 'MyPHPProxy/1.0 (PHP-cURL-Fetcher; mimics Node fetch)'); // Bir kullanıcı aracısı ayarla, bazı siteler bunu gerektirir
curl_setopt($ch, CURLOPT_TIMEOUT, 30);          // Tüm cURL işlemi için saniye cinsinden zaman aşımı
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);   // Bağlantı aşaması için saniye cinsinden zaman aşımı

// ÖNEMLİ: HTTP hata kodlarında (4xx, 5xx) başarısız olma. Hata sayfalarının gövdesini de almak istiyoruz.
// Node.js fetch() ayrıca HTTP hata kodlarında çözümlenir ve .text() çağrılmasına izin verir.
curl_setopt($ch, CURLOPT_FAILONERROR, false);

// Güvenlik için SSL sertifikasını doğrula. Yalnızca hata ayıklama veya belirli güvenilir dahili CA'lar için false olarak ayarla.
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);   // Ortak bir adın varlığını kontrol et ve ayrıca sağlanan ana bilgisayar adıyla eşleştiğini doğrula.

// cURL oturumunu çalıştır
$htmlContent = curl_exec($ch);
$curlErrorNum = curl_errno($ch);
$curlErrorMsg = curl_error($ch);

// cURL oturumunu kapat
curl_close($ch);

// cURL yürütme hatalarını kontrol et (örneğin, ağ sorunu, DNS çözümleme hatası, SSL el sıkışma hatası)
if ($curlErrorNum !== 0) {
    // Bu, Node.js sunucusundaki `catch (error)` bloğuna karşılık gelir
    http_response_code(500); // Internal Server Error (proxy'nin hatası veya hedefe ulaşılamaması)
    header('Content-Type: text/plain; charset=utf-8');
    $errorMessage = "Proxy tarafında hata oluştu (cURL Error #{$curlErrorNum}): " . htmlspecialchars($curlErrorMsg) . ". İstenen URL: " . htmlspecialchars($targetUrl);
    echo $errorMessage;
    // Hatayı sunucu tarafında günlüğe kaydet
    error_log("Proxy hatası (cURL) " . $targetUrl . " için: Error #{$curlErrorNum} - " . $curlErrorMsg);
    exit;
}

// cURL yürütmesi başarılıysa (hedef 404, 500 vb. döndürse bile),
// getirilen içeriği gönder. Proxy'nin kendisi 200 OK ile yanıt verir.
// İstemci tarafı script.js daha sonra proxy'nin yanıtının `response.ok` değerini kontrol edecektir.

echo $htmlContent;

?>