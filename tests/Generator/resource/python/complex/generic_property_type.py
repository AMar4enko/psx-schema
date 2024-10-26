from pydantic import BaseModel, Field, GetCoreSchemaHandler
from pydantic_core import CoreSchema, core_schema
from typing import Any, Dict, Generic, List, Optional, TypeVar, UserList, UserDict
from .property_type import PropertyType


# Represents a generic value which can be replaced with a dynamic type
class GenericPropertyType(PropertyType):
    type: Optional[str] = Field(default=None, alias="type")
    name: Optional[str] = Field(default=None, alias="name")
    pass


