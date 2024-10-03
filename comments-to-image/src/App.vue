<template>
  <div class="comments-container">
    <img src="../src/img/vinnie-puh.jpg" alt="Example image" class="image" />
    <h3>Комментарии</h3>

    <form @submit.prevent="addComment" class="comment-form">
      <input
        v-model="name"
        type="text"
        placeholder="Имя"
        required
        class="input-field"
      />
      <textarea
        v-model="commentText"
        placeholder="Текст комментария"
        required
        class="input-field textarea-field"
      ></textarea>
      <input
        v-model="captchaInput"
        type="text"
        placeholder="Введите капчу"
        required
        class="input-field"
      />
      <div class="captcha">
        <span>{{ captcha }}</span>
      </div>
      <button type="submit" class="submit-button">Добавить комментарий</button>
    </form>

    <ul class="comments-list">
      <li
        v-for="(comment, index) in comments"
        :key="comment.id"
        class="comment-item"
      >
        <div class="comment-data">
          <p class="comment-meta">
            <strong>{{ comment.name }}</strong> ({{ comment.created_at }}):
          </p>
          <p class="comment-text">{{ comment.comment_text }}</p>
        </div>
        <button @click="deleteComment(comment.id)" class="delete-button">
          Удалить
        </button>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from "axios"; // Импорт Axios

export default {
  name: "App",
  data() {
    return {
      name: "", // Имя пользователя
      commentText: "", // Текст комментария
      captcha: "", // Сгенерированная капча
      captchaInput: "", // Введенная капча
      comments: [], // Массив комментариев
    };
  },
  mounted() {
    this.loadComments(); // Загружаем комментарии при монтировании компонента
    this.generateCaptcha(); // Генерируем капчу при загрузке страницы
  },
  methods: {
    async loadComments() {
      try {
        const response = await axios.get(
          "http://localhost/comments-api/index.php/comments"
        ); // Получаем комментарии из API
        this.comments = response.data; // Сохраняем комментарии в состояние
      } catch (error) {
        console.error("Ошибка при загрузке комментариев:", error); // Логируем ошибку
      }
    },
    
    generateCaptcha() {
      // Генерация случайного числа для капчи
      this.captcha = Math.floor(1000 + Math.random() * 9000);
    },

    async addComment() {
      // Проверка, совпадает ли введенная капча с сгенерированной
      if (this.captchaInput != this.captcha) {
        alert("Неверная капча");
        this.generateCaptcha(); // Перегенерация капчи
        return;
      }

      // Создаем объект нового комментария
      const newComment = {
        name: this.name,
        comment_text: this.commentText,
      };

      try {
        await axios.post(
          "http://localhost/comments-api/index.php/comments",
          newComment
        ); // Отправка нового комментария на сервер

        // После добавления нового комментария загружаем все комментарии снова
        await this.loadComments(); // Обновляем список комментариев

        // Очищаем поля формы и генерируем новую капчу
        this.name = "";
        this.commentText = "";
        this.captchaInput = "";
        this.generateCaptcha(); // Генерируем новую капчу
      } catch (error) {
        console.error("Ошибка при добавлении комментария:", error); // Логируем ошибку
      }
    },

    async deleteComment(commentId) {
      try {
        await axios.delete(
          `http://localhost/comments-api/index.php/comments/${commentId}`
        ); // Удаляем комментарий по ID
        this.comments = this.comments.filter(
          (comment) => comment.id !== commentId
        ); // Обновляем массив комментариев
      } catch (error) {
        console.error("Ошибка при удалении комментария:", error); // Логируем ошибку
      }
    },
  },
};
</script>

<style scoped>
.comments-container {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  background-color: #f9f9f9;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.image {
  display: block;
  max-width: 450px;
  margin: 0 auto 20px;
  border-radius: 10px;
}

h3 {
  text-align: center;
  margin-bottom: 20px;
  color: #333;
}

.comment-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-bottom: 30px;
}

.input-field,
.textarea-field {
  font-family: "Helvetica Neue", Arial, sans-serif;
  font-size: 18px;
  font-weight: 400;
  line-height: 1.5;
  color: #333;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

.textarea-field {
  min-height: 100px;
  font-size: 16px;
}

.captcha {
  font-size: 18px;
  font-weight: bold;
  color: #555;
  text-align: center;
}

.submit-button {
  background-color: #28a745;
  color: white;
  padding: 10px 15px;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.submit-button:hover {
  background-color: #218838;
}

.comments-list {
  list-style-type: none;
  padding: 0;
  margin: 0;
}

.comment-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: white;
  padding: 15px;
  margin-bottom: 10px;
  border: 1px solid #eee;
  border-radius: 5px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.comment-data {
  font-family: "Helvetica Neue", Arial, sans-serif;
  display: flex;
  flex-direction: column;
}

.comment-meta {
  display: inline;
  font-size: 14px;
  font-weight: bold;
  color: #555;
}

.comment-text {
  display: inline;
  margin-top: 10px;
  font-size: 16px;
  color: #333;
  word-wrap: break-word; /* Автоматический перенос слов, если они слишком длинные */
  word-break: break-word; /* Перенос текста при выходе за границы контейнера */
  white-space: normal;
}

.delete-button {
  height: 40px;
  background-color: #dc3545;
  color: white;
  padding: 5px 10px;
  font-size: 14px;
  border: none;
  border-radius: 3px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-left: 10px;
}

.delete-button:hover {
  background-color: #c82333;
}
</style>
