import $ from 'jquery';

class JsTest {
  constructor() {
    this.clickAreas = $('.header, #main-content, .footer');
    this.events();
  }

  events() {
    this.clickAreas.on('click', this.sayHello);
  }

  sayHello() {
    alert('Hello from JS');
  }
}

export default JsTest;
