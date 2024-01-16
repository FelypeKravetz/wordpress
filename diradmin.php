<?php
/*
Plugin Name: DirAdmin
Description: Plugin para listar, navegar, gerenciar e fazer upload de arquivos em um diretório específico.
Version: 1.1
Author: Felype Kravetz
*/

// Defina o diretório base (ajuste conforme necessário)
define('DIRADMIN_BASE_DIRECTORY', ABSPATH); // ABSPATH é a constante que aponta para o diretório raiz do WordPress

// Adicione um menu ao painel administrativo
add_action('admin_menu', 'diradmin_menu');

function diradmin_menu() {
    add_menu_page('DirAdmin', 'DirAdmin', 'manage_options', 'diradmin', 'diradmin_page');
}

// Página de administração do plugin
function diradmin_page() {
    echo '<style>
        body {
            background-color: #1e1e1e;
            color: #ffffff;
            font-family: \'Arial\', sans-serif;
        }

        .wrap {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #2a2a2a;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        h2 {
            color: #ffcc00;
        }

        p {
            line-height: 1.6;
        }

        .notice {
            background-color: #0073aa;
            color: #ffffff;
        }

        .directory-link {
            color: #0073aa;
            text-decoration: none;
            font-weight: bold;
        }

        .category-title {
            color: #0073aa;
            margin-top: 10px;
        }

        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            display: inline-block;
            padding: 5px 10px;
            margin-right: 5px;
            background-color: #0073aa;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
        }

        .pagination a:hover {
            background-color: #004466;
        }

        .file-info {
            font-size: 0.8em;
            color: #888888;
        }

        .file-actions {
            margin-top: 10px;
        }

        .file-actions a {
            display: inline-block;
            margin-right: 10px;
            color: #0073aa;
            text-decoration: none;
        }

        .file-actions a:hover {
            text-decoration: underline;
        }

        .upload-form {
            margin-top: 20px;
        }

        .upload-form input[type="file"] {
            margin-right: 10px;
        }
    </style>';

    echo '<div class="wrap">';
    echo '<h2>DirAdmin - Listagem de Arquivos</h2>';

    // Diretório que você deseja listar
    $baseDirectory = defined('DIRADMIN_BASE_DIRECTORY') ? rtrim(DIRADMIN_BASE_DIRECTORY, '/') : ABSPATH;
    $requestedDirectory = isset($_GET['dir']) ? $_GET['dir'] : '';
    $currentDirectory = $baseDirectory . '/' . $requestedDirectory;

    // Se o formulário de upload foi enviado
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['upload_file']['name'])) {
        diradmin_process_upload($currentDirectory);
    }

    // Lista de arquivos no diretório
    $files = scandir($currentDirectory);
    $files = array_diff($files, array('..', '.')); // Remove os diretórios de navegação
    $files = array_values($files); // Reindexa o array

    // Agrupa os arquivos por tipo (diretório ou arquivo)
    $groupedFiles = array('directories' => array(), 'files' => array());
    foreach ($files as $file) {
        $filePath = $currentDirectory . '/' . $file;
        if (is_dir($filePath)) {
            $groupedFiles['directories'][] = $file;
        } else {
            $groupedFiles['files'][] = $file;
        }
    }

    // Exibe os diretórios
    if (!empty($groupedFiles['directories'])) {
        echo '<div class="category-title">Diretórios</div>';
        foreach ($groupedFiles['directories'] as $directory) {
            $directoryLink = esc_url(add_query_arg(array('dir' => $requestedDirectory . '/' . $directory)));
            echo '<p><a href="' . $directoryLink . '" class="directory-link">' . $directory . '</a></p>';
        }
    }

    // Exibe os arquivos
    if (!empty($groupedFiles['files'])) {
        echo '<div class="category-title">Arquivos</div>';
        foreach ($groupedFiles['files'] as $file) {
            $fileLink = esc_url(add_query_arg(array('dir' => $requestedDirectory, 'file' => $file)));
            echo '<p>';
            echo '<span>' . $file . '</span>';
            // Exibir informações sobre o arquivo
            $fileInfo = stat($currentDirectory . '/' . $file);
            echo '<span class="file-info">Tamanho: ' . $fileInfo['size'] . ' bytes | Última modificação: ' . date('Y-m-d H:i:s', $fileInfo['mtime']) . '</span>';
            echo '</p>';
            echo '<div class="file-actions">';
            echo '<a href="' . $fileLink . '&action=download">Download</a>';
            echo '<a href="' . $fileLink . '&action=delete" onclick="return confirm(\'Tem certeza que deseja excluir este arquivo?\')">Excluir</a>';
            echo '</div>';
        }
    }

    // Formulário de upload
    echo '<div class="upload-form">';
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="upload_file" />';
    echo '<input type="submit" value="Upload" />';
    echo '</form>';
    echo '</div>';

    echo '</div>';
}

function diradmin_process_upload($targetDirectory) {
    $targetFile = $targetDirectory . '/' . basename($_FILES['upload_file']['name']);

    // Move o arquivo para o diretório de destino
    if (move_uploaded_file($_FILES['upload_file']['tmp_name'], $targetFile)) {
        echo '<p class="notice">O arquivo ' . htmlspecialchars(basename($_FILES['upload_file']['name'])) . ' foi enviado com sucesso.</p>';
    } else {
        echo '<p class="notice">Desculpe, ocorreu um erro ao enviar o arquivo.</p>';
    }
}
?>
