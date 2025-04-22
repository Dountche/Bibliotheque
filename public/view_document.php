<?php
session_start();
$file = $_GET['file'] ?? '';
$filePath = __DIR__ . '/documents/' . basename($file);
if (!file_exists($filePath)) {
    http_response_code(404);
    exit('Fichier non trouvé.');
}
// Construction de l’URL absolue vers le document
$scheme = isset($_SERVER['HTTPS']) ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];
$base   = dirname($_SERVER['PHP_SELF']);
$fileUrl= "$scheme://$host$base/documents/$file";
$ext    = strtolower(pathinfo($file, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Visualiseur de document</title>
  <style>
    html, body { margin:0; padding:0; height:100%; }
  </style>
</head>
<body>
  <!-- Lien caché qui ouvrira le vrai document -->
  <?php if ($ext === 'pdf'): ?>
    <?php $openUrl = "documents/".rawurlencode($file)."#toolbar=0"; ?>
  <?php elseif (in_array($ext, ['doc','docx','ppt','pptx'])): ?>
    <?php $openUrl = "https://docs.google.com/viewer?url=".rawurlencode($fileUrl)."&embedded=true"; ?>
  <?php else: ?>
    <?php $openUrl = "documents/".rawurlencode($file); ?>
  <?php endif; ?>

  <a id="autoOpenLink"
     href="<?= htmlspecialchars($openUrl) ?>"
     style="display:none;"
  >Open</a>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const link = document.getElementById('autoOpenLink');
      if (link) {
        // 1) ouvrir le document dans un nouvel onglet
        link.click();
        // 2) fermer cette vue après un petit délai
        // setTimeout(() => window.close(), 300);
      }
    });
  </script>
</body>
</html>
