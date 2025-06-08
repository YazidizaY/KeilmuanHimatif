</main>
            <?php

            if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'STUDENT' && ($current_page ?? '') == 'student_dashboard') {
                echo '<footer class="student-prompt-bar-container">';
                echo '  <div class="fake-prompt-bar">';
                echo '    <div class="prompt-text-display" id="triggerRequestModalFromPrompt">Ketik permintaan sesi tutor di sini...</div>'; // Teks placeholder
                echo '    <button class="btn-trigger-modal" id="sendRequestFromPromptBtn" aria-label="Kirim Permintaan"><span class="nav-icon">➕</span></button>'; // Atau ikon kirim seperti panah
                echo '  </div>';
                echo '</footer>';
            }
            ?>

        </div>
    </div>

    <?php
    $js_base_path = "/" . basename(dirname(dirname(__FILE__)));
    if ($js_base_path == "/your_project_root") $js_base_path = ""; 
    ?>
    <script src="<?php echo $js_base_path; ?>/js/script.js"></script>
    <?php
        if (isset($page_specific_js)) {
            echo '<script src="' . $js_base_path . '/js/' . $page_specific_js . '"></script>';
        }
    ?>
</body>
</html>