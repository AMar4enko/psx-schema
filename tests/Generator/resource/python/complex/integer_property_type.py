from pydantic import BaseModel, Field, GetCoreSchemaHandler
from pydantic_core import CoreSchema, core_schema
from typing import Any, Dict, Generic, List, Optional, TypeVar, UserList, UserDict
from .scalar_property_type import ScalarPropertyType


# Represents an integer value
class IntegerPropertyType(ScalarPropertyType):
    type: Optional[str] = Field(default=None, alias="type")
    pass


