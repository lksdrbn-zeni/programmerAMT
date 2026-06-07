(function () {
  'use strict';

  const questions = Array.isArray(window.psychologySiteData?.questions) ? window.psychologySiteData.questions : [];
  const materialModalElement = document.getElementById('materialModal');
  const materialModal = materialModalElement ? new bootstrap.Modal(materialModalElement) : null;

  document.querySelectorAll('#categoryTabs .nav-link').forEach((button) => {
    button.addEventListener('click', () => {
      const filter = button.dataset.filter || 'all';
      document.querySelectorAll('#categoryTabs .nav-link').forEach((item) => item.classList.remove('active'));
      button.classList.add('active');
      document.querySelectorAll('.material-item').forEach((item) => {
        item.classList.toggle('d-none', filter !== 'all' && item.dataset.category !== filter);
      });
    });
  });

  document.querySelectorAll('.open-material').forEach((button) => {
    button.addEventListener('click', () => {
      const card = button.closest('.theory-card');
      if (!card || !materialModal) return;
      setText('materialModalLabel', card.dataset.title || 'Материал');
      setText('materialModalText', card.dataset.text || '');
      setText('materialModalExample', card.dataset.example || 'Практический пример будет добавлен преподавателем.');
      materialModal.show();
    });
  });

  function renderQuiz() {
    const root = document.getElementById('knowledgeQuiz');
    if (!root) return;
    if (!questions.length) {
      root.innerHTML = '<div class="alert alert-warning rounded-4 border-0">Вопросы пока не добавлены. Откройте админ-панель WordPress и создайте записи в разделе «Психология: тесты».</div>';
      return;
    }
    root.innerHTML = questions.map((item, index) => {
      const options = (item.options || []).map((option, optionIndex) => `
        <label class="quiz-option">
          <input type="radio" name="knowledge_${index}" value="${optionIndex}">
          <span>${escapeHtml(option)}</span>
        </label>
      `).join('');
      return `
        <div class="quiz-item" id="knowledgeItem_${index}">
          <h4>${index + 1}. ${escapeHtml(item.question)}</h4>
          <div class="quiz-options">${options}</div>
        </div>
      `;
    }).join('');
    setText('knowledgeCountBadge', `${questions.length} вопросов`);
  }

  function checkKnowledgeQuiz() {
    let answered = 0;
    let score = 0;
    questions.forEach((item, index) => {
      const selected = document.querySelector(`input[name="knowledge_${index}"]:checked`);
      const block = document.getElementById(`knowledgeItem_${index}`);
      block?.classList.remove('border-success', 'border-danger');
      if (!selected) return;
      answered += 1;
      if (Number(selected.value) === Number(item.correct)) {
        score += 1;
        block?.classList.add('border-success');
      } else {
        block?.classList.add('border-danger');
      }
    });
    const percent = questions.length ? Math.round((score / questions.length) * 100) : 0;
    let message = 'Нужно повторить теорию и попробовать ещё раз.';
    if (percent >= 85) message = 'Отлично! Материал усвоен уверенно.';
    else if (percent >= 60) message = 'Хорошо, но некоторые темы стоит повторить.';
    const result = document.getElementById('knowledgeResult');
    if (result) {
      result.innerHTML = `
        <div class="result-card">
          <h4 class="mb-2">Результат: ${score} из ${questions.length}</h4>
          <p class="mb-2">Отвечено вопросов: ${answered} из ${questions.length}. Процент выполнения: <strong>${percent}%</strong>.</p>
          <div class="progress rounded-pill mb-3" role="progressbar" aria-valuenow="${percent}" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-success" style="width:${percent}%"></div></div>
          <p class="mb-0">${message}</p>
        </div>`;
    }
  }

  function resetKnowledgeQuiz() {
    document.querySelectorAll('#knowledgeQuiz input[type="radio"]').forEach((input) => { input.checked = false; });
    document.querySelectorAll('.quiz-item').forEach((item) => item.classList.remove('border-success', 'border-danger'));
    const result = document.getElementById('knowledgeResult');
    if (result) result.innerHTML = '';
  }

  document.getElementById('checkKnowledge')?.addEventListener('click', checkKnowledgeQuiz);
  document.getElementById('resetKnowledge')?.addEventListener('click', resetKnowledgeQuiz);

  function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = value;
  }

  function escapeHtml(value) {
    return String(value ?? '')
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;');
  }

  renderQuiz();
})();
