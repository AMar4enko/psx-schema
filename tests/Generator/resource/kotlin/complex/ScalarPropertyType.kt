/**
 * Base scalar property type
 */
open abstract class ScalarPropertyType : PropertyType {
    @JsonProperty("type") var type: String? = null
}

