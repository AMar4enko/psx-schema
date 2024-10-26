using System.Text.Json.Serialization;

/// <summary>
/// Represents a float value
/// </summary>
public class NumberPropertyType : ScalarPropertyType
{
    [JsonPropertyName("type")]
    public string? Type { get; set; }

}

