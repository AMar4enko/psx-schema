/**
 * Location of the person
 */
open class Location {
    var lat: Float? = null
    var long: Float? = null
}

/**
 * An application
 */
open class Web {
    var name: String? = null
    var url: String? = null
}

/**
 * An simple author element with some description
 */
open class Author {
    var title: String? = null
    var email: String? = null
    var categories: Array<String>? = null
    var locations: Array<Location>? = null
    var origin: Location? = null
}

open class Meta : HashMap<String, String>() {
}

/**
 * An general news entry
 */
open class News {
    var config: Meta? = null
    var inlineConfig: Map<String, String>? = null
    var mapTags: Map<String, String>? = null
    var mapReceiver: Map<String, Author>? = null
    var mapResources: Map<String, Any>? = null
    var tags: Array<String>? = null
    var receiver: Array<Author>? = null
    var resources: Array<Any>? = null
    var profileImage: ByteArray? = null
    var read: Boolean? = null
    var source: Any? = null
    var author: Author? = null
    var meta: Meta? = null
    var sendDate: java.time.LocalDate? = null
    var readDate: java.time.LocalDateTime? = null
    var expires: String? = null
    var range: String? = null
    var price: Float? = null
    var rating: Int? = null
    var content: String? = null
    var question: String? = null
    var version: String? = null
    var coffeeTime: java.time.LocalTime? = null
    var profileUri: String? = null
    var captcha: String? = null
    var mediaFields: String? = null
    var payload: Any? = null
}
