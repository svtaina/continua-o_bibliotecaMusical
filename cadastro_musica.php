<?php
/*
 * =============================================
 * ARQUIVO: cadastro_musica.php (versão 1 — só leitura + função do YouTube)
 * =============================================
 */
include('valida_sessao.php');
include('conexao.php');

// -----------------------------------------------
// FUNÇÃO: Extrair ID do vídeo do YouTube
// -----------------------------------------------
// O YouTube aceita vários formatos de URL.
// Esta função extrai o ID de 11 caracteres de qualquer um.
//
// URLs suportadas:
//   https://www.youtube.com/watch?v=dQw4w9WgXcQ  → ID = dQw4w9WgXcQ
//   https://youtu.be/dQw4w9WgXcQ
//   https://www.youtube.com/embed/dQw4w9WgXcQ
function getYoutubeVideoId(string $url) {
    $patterns = [
        '/youtube\.com\/watch\?v=([^\&\?\/]+)/',
        '/youtube\.com\/embed\/([^\&\?\/]+)/',
        '/youtube\.com\/v\/([^\&\?\/]+)/',
        '/youtu\.be\/([^\&\?\/]+)/'
    ];
    foreach ($patterns as $p) {
        // preg_match() testa a expressão regular; $m[1] = primeiro grupo ()
        if (preg_match($p, $url, $m)) return $m[1];
    }
    return "";
}

// Filtra por um artista específico (via ?artista_id=X vindo de "🎵 Músicas")
$artistas          = $conn->query("SELECT id, nome FROM artistas ORDER BY nome");
$artista_id_filtro = isset($_GET['artista_id']) ? intval($_GET['artista_id']) : null;

$musicas = null;
if ($artista_id_filtro) {
    $stmt = $conn->prepare("
        SELECT m.*, a.nome AS artista_nome
        FROM musicas m
        JOIN artistas a ON m.artista_id = a.id
        WHERE m.artista_id = ?
        ORDER BY m.titulo
    ");
    $stmt->bind_param("i", $artista_id_filtro);
    $stmt->execute();
    $musicas = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Músicas — Sistema Musical</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 100 100%27><text y=%27.9em%27 font-size=%2790%27>🎵</text></svg>">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">

    <div class="page-header-bar">
        <h2>🎵 Músicas</h2>
        <div class="header-actions">
            <a href="cadastro_artista.php" class="btn-back">⬅ Voltar para Artistas</a>
        </div>
    </div>

    <?php if ($musicas && $musicas->num_rows > 0): ?>
        <h3>🎵 Músicas Cadastradas</h3>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Álbum</th>
                        <th class="col-youtube">YouTube</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $musicas->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($row['album']) ?: '—'; ?></td>
                            <td>
                                <?php
                                $vid = getYoutubeVideoId($row['youtube_link'] ?? '');
                                if ($vid):
                                    $watch_url = "https://www.youtube.com/watch?v={$vid}";
                                ?>
                                    <a href="<?php echo $watch_url; ?>" target="_blank"
                                       rel="noopener noreferrer" class="btn-youtube">▶ Assistir</a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php elseif ($artista_id_filtro): ?>
        <p class="text-muted">Nenhuma música cadastrada para este artista ainda.</p>
    <?php else: ?>
        <p class="text-muted">Acesse esta página a partir de "🎵 Músicas" na lista de artistas.</p>
    <?php endif; ?>

</div>
</body>
</html>
