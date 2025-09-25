<?php

defined('C5_EXECUTE') or die('Access Denied.');

if ($view->controller->getAction() === 'view_contents') {
?>

    <?php
    $a = new Area('Main');
    $a->setAreaGridMaximumColumns(12);
    $a->display($c);
    ?>

<?php } else { ?>

    <div id="ccm-stack-editor" class="d-none">
        <div class="container">
            <div class="row justify-content-center">
                <div id="editorColumn" class="ccm-stack-editor-column col-12">
                    <div class="card">
                        <div class="card-header d-flex">
                            <?=$breadcrumb->render()?>
                            <?php if (!$stack->isEditMode()) { ?>
                                <div class="ms-auto d-flex gap-2" id="editorControls">
                                    <!-- Icons will be inserted dynamically -->
                                </div>
                            <?php } ?>
                        </div>
                        <div class="card-body">
                            <?php if ($stack->isEditMode()) { ?>
                                <?php
                                $a = new Area('Main');
                                $a->setAreaGridMaximumColumns(12);
                                $a->display($c);
                                ?>
                            <?php } else { ?>
                                <div class="text-center p-5" id="ccm-stack-editor-frame-loader">
                                    <i class="fa fa-spin fa-spinner fa-3x"></i>
                                </div>
                                <iframe class="d-none w-100" id="ccm-stack-editor-frame" src="<?=$view->action('view_contents')?>" frameborder="0"></iframe>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {

            <?php
            if (!empty($error)) { ?>
                ConcreteAlert.error({
                    'message': <?= json_encode($error) ?>,
                })
            <?php } elseif (!empty($success)) { ?>
                ConcreteAlert.notify({
                    'message': <?= json_encode($success) ?>,
                })
            <?php } ?>

            const editorColumn = document.getElementById("editorColumn");
            const controls = document.getElementById("editorControls");
            const frame = document.getElementById("ccm-stack-editor-frame");
            const loader = document.getElementById("ccm-stack-editor-frame-loader");

            let editorWidth = "large"; // small, medium, large

            function updateEditorClasses() {
                document.getElementById("ccm-stack-editor").classList.remove("d-none");
                editorColumn.className = "ccm-stack-editor-column";
                if (editorWidth === "small") {
                    editorColumn.classList.add("col-4");
                } else if (editorWidth === "medium") {
                    editorColumn.classList.add("col-8");
                } else {
                    editorColumn.classList.add("col-12");
                }
                updateControls();
            }

            function updateControls() {
                controls.innerHTML = ""; // clear old buttons

                if (editorWidth === "large") {
                    // only compress → medium
                    controls.appendChild(makeButton("fa-compress-arrows-alt", () => {
                        editorWidth = "medium";
                        updateEditorClasses();
                    }));
                } else if (editorWidth === "medium") {
                    // expand → large
                    controls.appendChild(makeButton("fa-expand-arrows-alt", () => {
                        editorWidth = "large";
                        updateEditorClasses();
                    }));
                    // compress → small
                    controls.appendChild(makeButton("fa-compress-arrows-alt", () => {
                        editorWidth = "small";
                        updateEditorClasses();
                    }));
                } else if (editorWidth === "small") {
                    // only expand → large
                    controls.appendChild(makeButton("fa-expand-arrows-alt", () => {
                        editorWidth = "large";
                        updateEditorClasses();
                    }));
                }
            }

            function makeButton(iconClass, handler) {
                const a = document.createElement("a");
                a.href = "#";
                a.className = "ccm-hover-icon";
                a.innerHTML = `<i class="fa ${iconClass}"></i>`;
                a.addEventListener("click", function(e) {
                    e.preventDefault();
                    handler();
                });
                return a;
            }

            // Initial setup
            updateEditorClasses();

            // iframe loader handling
            if (frame) {
                frame.addEventListener("load", function() {
                    const doc = frame.contentDocument || frame.contentWindow.document;
                    loader.classList.add("d-none");
                    frame.classList.remove("d-none");
                    frame.style.height = doc.documentElement.scrollHeight + "px";
                });
            }
        });
    </script>

<?php } ?>