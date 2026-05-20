import $ from 'jquery';
import waypoints from '../../../node_modules/waypoints/lib/noframework.waypoints';

// BackTop controls the floating "back to top" link rendered in footer.php.
// It becomes visible once the visitor scrolls past #main-content.
class BackTop {
  constructor(){
    // Cached DOM hooks used by the waypoint behavior.
    this.lazyImages = $(".lazyload");
    this.backTopBtn = $(".backtop");
    this.triggerElement = $("#main-content");
    this.createOptionsWaypoint();
    this.refreshWaypoints();
  }

  


  createOptionsWaypoint() {
    if (!this.triggerElement.length) return;

    var that = this;
    // Waypoint watches the main content boundary and toggles the visible state.
    new Waypoint({
      element: this.triggerElement[0],
      handler: function(direction) {
        if (direction == "down"){
          that.backTopBtn.addClass("backtop--is-visible");
        }else{
          that.backTopBtn.removeClass("backtop--is-visible");
        }
      }
    })
  }

  refreshWaypoints() {
    // Recalculate waypoint positions after page assets/layout have settled.
    if (typeof Waypoint !== "undefined") {
      Waypoint.refreshAll();
    }
  }


}

export default BackTop;
