from pydantic import BaseModel, Field, GetCoreSchemaHandler
from pydantic_core import CoreSchema, core_schema
from typing import Any, Dict, Generic, List, Optional, TypeVar, UserList, UserDict
from .human_type import HumanType


class HumanType(BaseModel):
    first_name: Optional[str] = Field(default=None, alias="firstName")
    parent: Optional[HumanType] = Field(default=None, alias="parent")
    pass


