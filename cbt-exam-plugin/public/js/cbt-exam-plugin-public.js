(function( $ ) {
    'use strict';

    $(function() {
        const examWrapper = $('#cbt-exam-wrapper');
        if (examWrapper.length) {
            const duration = parseInt(examWrapper.data('duration')) * 60;
            const timeDisplay = $('#cbt-time-display');
            const questions = $('.cbt-question');
            const totalQuestions = questions.length;
            const prevBtn = $('#cbt-prev-btn');
            const nextBtn = $('#cbt-next-btn');
            const submitBtn = $('#cbt-submit-btn');
            const progressBar = $('#cbt-progress');
            const form = $('#cbt-exam-form');

            let currentQuestion = 0;
            let questionTimer;

            function startQuestionTimer(questionElement) {
                clearInterval(questionTimer);
                const timeLimit = parseInt(questionElement.data('time-limit'));
                const timerDisplay = questionElement.find('.cbt-question-time-display');

                if (isNaN(timeLimit) || timeLimit <= 0) {
                    timerDisplay.text('No limit');
                    return;
                }

                let remainingTime = timeLimit;

                questionTimer = setInterval(function() {
                    const minutes = Math.floor(remainingTime / 60);
                    const secs = remainingTime % 60;

                    timerDisplay.text(
                        `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`
                    );

                    if (--remainingTime < 0) {
                        clearInterval(questionTimer);
                        timerDisplay.text('Time Up!');
                        // Auto-advance to next question
                        if (currentQuestion < totalQuestions - 1) {
                            currentQuestion++;
                            showQuestion(currentQuestion);
                        } else {
                            // If it's the last question, submit the exam
                            submitExam();
                        }
                    }
                }, 1000);
            }

            function updateProgressBar() {
                const progress = ((currentQuestion + 1) / totalQuestions) * 100;
                progressBar.css('width', progress + '%');
            }

            function showQuestion(index) {
                questions.hide();
                const currentQuestionEl = $(questions[index]);
                currentQuestionEl.show();
                updateProgressBar();
                startQuestionTimer(currentQuestionEl);

                prevBtn.toggle(index > 0);
                nextBtn.toggle(index < totalQuestions - 1);
                submitBtn.toggle(index === totalQuestions - 1);
            }

            function submitExam() {
                clearInterval(questionTimer);
                const formData = form.serialize() + '&action=' + cbtExamData.action + '&nonce=' + cbtExamData.nonce;

                $.post(cbtExamData.ajax_url, formData, function(response) {
                    if (response.success) {
                        let resultHtml;
                        if (response.data.status === 'pending') {
                            resultHtml = `
                                <div class="cbt-results">
                                    <h2>${cbtExamData.text.results}</h2>
                                    <p>${cbtExamData.text.pending_review}</p>
                                    <p>${cbtExamData.text.objective_score} ${response.data.score} / ${response.data.total}</p>
                                </div>
                            `;
                        } else {
                            resultHtml = `
                                <div class="cbt-results">
                                    <h2>${cbtExamData.text.results}</h2>
                                    <p>${cbtExamData.text.scored} ${response.data.score} / ${response.data.total}</p>
                                    <p>${cbtExamData.text.percentage} ${response.data.percentage}%</p>
                                    <p><strong>${response.data.passed ? cbtExamData.text.passed : cbtExamData.text.failed}</strong></p>
                                </div>
                            `;
                        }
                        examWrapper.html(resultHtml);
                    } else {
                        alert('An error occurred: ' + response.data.message);
                    }
                }).fail(function() {
                    alert('An error occurred while submitting the exam.');
                });
            }

            nextBtn.on('click', function() {
                if (currentQuestion < totalQuestions - 1) {
                    currentQuestion++;
                    showQuestion(currentQuestion);
                }
            });

            prevBtn.on('click', function() {
                if (currentQuestion > 0) {
                    currentQuestion--;
                    showQuestion(currentQuestion);
                }
            });

            submitBtn.on('click', function() {
                if (confirm('Are you sure you want to submit the exam?')) {
                    submitExam();
                }
            });

            // Initialize
            showQuestion(currentQuestion);
        }
    });

})( jQuery );
